const API_BASE = '/api';
const TOKEN_KEY = 'amv_award_token_v2';

const state = {
  token: localStorage.getItem(TOKEN_KEY),
  user: null,
  hasVoted: false,
  candidates: [],
  ranking: [],
};

const els = {
  loginSection: document.getElementById('login-card'),
  appSection: document.getElementById('dashboard-card'),
  rankingSection: document.getElementById('ranking-card'),
  adminSection: document.getElementById('admin-card'),
  status: document.getElementById('status'),
  loginForm: document.getElementById('login-form'),
  identifier: document.getElementById('identifier'),
  password: document.getElementById('password'),
  userName: document.getElementById('user-name'),
  userRole: document.getElementById('user-role'),
  voteBadge: document.getElementById('vote-badge'),
  candidateList: document.getElementById('candidate-list'),
  rankingList: document.getElementById('ranking-list'),
  logoutBtn: document.getElementById('logout'),
  adminCreateForm: document.getElementById('admin-create-form'),
  adminUsers: document.getElementById('admin-users'),
};

async function api(path, options = {}) {
  const headers = {
    'Content-Type': 'application/json',
    ...(options.headers || {}),
  };
  if (state.token) {
    headers.Authorization = `Bearer ${state.token}`;
  }

  let response;
  try {
    response = await fetch(`${API_BASE}${path}`, {
      ...options,
      headers,
    });
  } catch (err) {
    throw new Error('No se pudo contactar al servidor');
  }

  let data = {};
  try {
    data = await response.json();
  } catch (err) {
    data = {};
  }

  if (response.status === 401) {
    logout(true);
    throw new Error('Sesión expirada, volvés a ingresar.');
  }

  if (!response.ok) {
    throw new Error(data.error || 'Ocurrió un error');
  }

  return data;
}

function setStatus(type, message) {
  if (!els.status) return;
  els.status.className = `status ${type}`;
  els.status.textContent = message;
  els.status.classList.remove('hidden');
}

function clearStatus() {
  els.status.classList.add('hidden');
}

function showLogin() {
  els.loginSection.classList.remove('hidden');
  els.appSection.classList.add('hidden');
  els.rankingSection.classList.add('hidden');
}

function showApp() {
  els.loginSection.classList.add('hidden');
  els.appSection.classList.remove('hidden');
  els.rankingSection.classList.remove('hidden');
  if (state.user && state.user.role.toLowerCase() === 'encargado') {
    els.adminSection.classList.remove('hidden');
  } else {
    els.adminSection.classList.add('hidden');
  }
}

async function bootstrap() {
  if (!state.token) {
    showLogin();
    return;
  }

  try {
    const data = await api('/me');
    state.user = data.user;
    state.hasVoted = data.has_voted;
    showApp();
    renderUser();
    const tasks = [loadCandidates(), loadRanking()];
    if (state.user.role.toLowerCase() === 'encargado') {
      tasks.push(loadAdminUsers());
    }
    await Promise.all(tasks);
  } catch (err) {
    console.error(err);
    logout(true);
    showLogin();
  }
}

function renderUser() {
  els.userName.textContent = state.user?.name || '';
  els.userRole.textContent = `${state.user?.role || ''} · peso ${state.user?.vote_weight || 1}`;
  els.voteBadge.textContent = state.hasVoted ? 'Ya votaste' : 'Voto pendiente';
  els.voteBadge.className = `pill ${state.hasVoted ? '' : 'pending'}`;
}

async function loadCandidates() {
  const data = await api('/candidates');
  state.candidates = data.candidates || [];
  renderCandidates();
}

function renderCandidates() {
  if (!state.candidates.length) {
    els.candidateList.innerHTML = '<div class="status">No hay candidatos cargados aún.</div>';
    return;
  }

  const cards = state.candidates
    .map(
      (c) => `
        <div class="card vote-card">
          <div class="name">${c.name}</div>
          <div class="pill">Sector: ${c.sector || '—'}</div>
          <button data-id="${c.id}" ${state.hasVoted ? 'disabled' : ''}>${state.hasVoted ? 'Voto emitido' : 'Votar a ' + c.name}</button>
        </div>
      `
    )
    .join('');

  els.candidateList.innerHTML = cards;

  els.candidateList.querySelectorAll('button[data-id]').forEach((btn) => {
    btn.addEventListener('click', () => submitVote(Number(btn.dataset.id), btn));
  });
}

async function submitVote(candidateId, btn) {
  if (state.hasVoted) {
    setStatus('error', 'Tu voto ya fue registrado.');
    return;
  }

  btn.disabled = true;
  setStatus('info', 'Enviando tu voto...');

  try {
    const res = await api('/vote', {
      method: 'POST',
      body: JSON.stringify({ candidate_id: candidateId }),
    });
    state.hasVoted = true;
    setStatus('success', `Voto registrado para ${res.candidate.name}. Peso: ${res.weight} puntos.`);
    renderUser();
    renderCandidates();
    await Promise.all([loadRanking(), loadAdminUsersIfNeeded()]);
  } catch (err) {
    btn.disabled = false;
    setStatus('error', err.message);
  }
}

async function loadRanking() {
  try {
    const data = await api('/ranking');
    state.ranking = data.ranking || [];
    renderRanking();
  } catch (err) {
    setStatus('error', err.message);
  }
}

function renderRanking() {
  if (!state.ranking.length) {
    els.rankingList.innerHTML = '<div class="status">Todavía no hay votos registrados.</div>';
    return;
  }

  const rows = state.ranking
    .map(
      (row, idx) => `
        <div class="ranking-row">
          <div class="pos">${idx + 1}</div>
          <div class="name">${row.name} <span class="pill">${row.sector || '—'}</span></div>
          <div class="points">${row.points} pts · ${row.total_votes} votos (${row.double_votes} dobles)</div>
          <div class="bar"><span style="width:${row.percentage}%"></span></div>
        </div>
      `
    )
    .join('');

  els.rankingList.innerHTML = rows;
}

function logout(silent = false) {
  const tokenBeforeClear = state.token;
  if (!silent && tokenBeforeClear) {
    api('/logout', { method: 'POST' }).catch(() => {});
  }
  localStorage.removeItem(TOKEN_KEY);
  state.token = null;
  state.user = null;
  state.hasVoted = false;
  showLogin();
}

async function loadAdminUsersIfNeeded() {
  if (state.user && state.user.role.toLowerCase() === 'encargado') {
    await loadAdminUsers();
  }
}

async function loadAdminUsers() {
  if (!els.adminUsers) return;
  try {
    const data = await api('/admin/users');
    renderAdminUsers(data.users || []);
  } catch (err) {
    setStatus('error', err.message);
  }
}

function renderAdminUsers(users) {
  if (!users.length) {
    els.adminUsers.innerHTML = '<div class="status">Sin usuarios cargados.</div>';
    return;
  }
  const rows = users
    .map(
      (u) => `
        <div class="ranking-row">
          <div class="pos">#${u.id}</div>
          <div class="name">${u.name} <span class="pill">${u.role}</span><span class="pill">${u.email}</span></div>
          <div class="points">${u.ci} · ${u.sector || '—'} · ${u.is_active ? 'Activo' : 'Inactivo'}</div>
          <button class="secondary" data-delete="${u.id}" ${u.id === state.user?.id ? 'disabled' : ''}>Eliminar</button>
        </div>
      `
    )
    .join('');
  els.adminUsers.innerHTML = rows;
  els.adminUsers.querySelectorAll('button[data-delete]').forEach((btn) => {
    btn.addEventListener('click', () => deleteUser(btn.dataset.delete));
  });
}

async function deleteUser(id) {
  if (!confirm('¿Eliminar este usuario?')) return;
  try {
    await api('/admin/users/delete', {
      method: 'POST',
      body: JSON.stringify({ id: Number(id) }),
    });
    setStatus('success', 'Usuario eliminado');
    await loadAdminUsers();
  } catch (err) {
    setStatus('error', err.message);
  }
}

if (els.adminCreateForm) {
  els.adminCreateForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearStatus();
    const formData = new FormData(els.adminCreateForm);
    const payload = Object.fromEntries(formData.entries());
    try {
      await api('/admin/users', {
        method: 'POST',
        body: JSON.stringify(payload),
      });
      setStatus('success', 'Usuario creado');
      els.adminCreateForm.reset();
      await loadAdminUsers();
    } catch (err) {
      setStatus('error', err.message);
    }
  });
}

els.loginForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  clearStatus();
  const identifier = els.identifier.value.trim();
  const password = els.password.value;
  if (!identifier || !password) {
    setStatus('error', 'Completá tus datos');
    return;
  }
  try {
    const data = await api('/login', {
      method: 'POST',
      body: JSON.stringify({ identifier, password }),
    });
    state.token = data.token;
    localStorage.setItem(TOKEN_KEY, data.token);
    state.user = data.user;
    state.hasVoted = data.has_voted;
    setStatus('success', 'Sesión iniciada');
    showApp();
    renderUser();
    await Promise.all([loadCandidates(), loadRanking()]);
    els.loginForm.reset();
  } catch (err) {
    setStatus('error', err.message);
  }
});

els.logoutBtn.addEventListener('click', () => logout());

bootstrap();
