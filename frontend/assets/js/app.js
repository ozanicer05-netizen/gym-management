const BASE_PATH = '/gym';

async function apiGet(path, params = {}) {
  const qs = new URLSearchParams(params).toString();
  const url = qs ? `${path}?${qs}` : path;

  const response = await fetch(url, {
    headers: { Accept: 'application/json' },
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  const payload = await response.json();

  if (!payload.ok) {
    throw new Error(payload.error || 'API error');
  }

  return payload;
}

async function apiSend(method, path, params = {}, body = null) {
  const qs = new URLSearchParams(params).toString();
  const url = qs ? `${path}?${qs}` : path;

  const response = await fetch(url, {
    method,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: body === null ? null : JSON.stringify(body),
  });

  const payload = await response.json().catch(() => ({ ok: false, error: `HTTP ${response.status}` }));

  if (!response.ok || !payload.ok) {
    throw new Error(payload.error || payload.details || `HTTP ${response.status}`);
  }

  return payload;
}

const apiPost = (path, body) => apiSend('POST', path, {}, body);
const apiPut = (path, id, body) => apiSend('PUT', path, { id }, body);
const apiDelete = (path, id) => apiSend('DELETE', path, { id });

async function loadLookup(type) {
  const payload = await apiGet('/gym/backend/api/lookups.php', { type });
  return payload.data || [];
}

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function showToast(message, type = 'success') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1080';
    document.body.appendChild(container);
  }

  const bg = type === 'error' ? 'bg-danger' : (type === 'info' ? 'bg-info' : 'bg-success');
  const toast = document.createElement('div');
  toast.className = `toast align-items-center text-white ${bg} border-0 show`;
  toast.setAttribute('role', 'alert');
  toast.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${escapeHtml(message)}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" aria-label="Close"></button>
    </div>`;
  toast.querySelector('.btn-close').addEventListener('click', () => toast.remove());
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 4000);
}

function confirmAction(message) {
  return window.confirm(message);
}
