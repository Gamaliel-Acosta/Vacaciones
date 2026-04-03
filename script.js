const appConfig = window.APP_CONFIG || {};
const apiUrl = appConfig.apiUrl || 'api.php';
const startDate = new Date(`${appConfig.startDate || '2026-03-27'}T00:00:00`);
const endDate = new Date(`${appConfig.endDate || '2026-04-10'}T23:59:59`);
const today = new Date();
today.setHours(0, 0, 0, 0);

const monthLabels = [
  'enero',
  'febrero',
  'marzo',
  'abril',
  'mayo',
  'junio',
  'julio',
  'agosto',
  'septiembre',
  'octubre',
  'noviembre',
  'diciembre',
];

const pagePath = window.location.pathname.split('/').pop() || 'index.php';
const isHomePage = pagePath === 'index.php';
const isDayPage = pagePath === 'day.php';

function formatDateISO(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function formatShortDate(date) {
  return `${date.getDate()} de ${monthLabels[date.getMonth()]} de ${date.getFullYear()}`;
}

function formatLongDate(date) {
  const weekday = new Intl.DateTimeFormat('es-MX', { weekday: 'long' }).format(date);
  return `${weekday.charAt(0).toUpperCase() + weekday.slice(1)}, ${formatShortDate(date)}`;
}

function parseDateValue(value) {
  if (!value) {
    return null;
  }
  const parsed = new Date(`${value}T00:00:00`);
  return Number.isNaN(parsed.getTime()) ? null : parsed;
}

function createDayLink(dateISO) {
  return `day.php?date=${encodeURIComponent(dateISO)}`;
}

function isFutureDate(date) {
  return date.getTime() > today.getTime();
}

async function apiRequest(action, options = {}) {
  const { method = 'GET', body = null, query = {} } = options;
  const url = new URL(apiUrl, window.location.href);
  url.searchParams.set('action', action);

  Object.entries(query).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      url.searchParams.set(key, value);
    }
  });

  const response = await fetch(url.toString(), { method, body });
  const data = await response.json();
  if (!response.ok || !data.ok) {
    throw new Error(data.message || 'No se pudo completar la operación.');
  }
  return data;
}

function getRangeDates() {
  const result = [];
  const current = new Date(startDate);
  current.setHours(0, 0, 0, 0);

  while (current <= endDate) {
    result.push(new Date(current));
    current.setDate(current.getDate() + 1);
  }

  return result;
}

async function setupHomeDescription() {
  const form = document.getElementById('homeDescriptionForm');
  const input = document.getElementById('homeDescriptionInput');
  const editButton = document.getElementById('homeDescriptionEdit');
  const status = document.getElementById('homeDescriptionStatus');
  const preview = document.getElementById('homeDescriptionPreview');

  if (!form || !input || !editButton || !status || !preview) {
    return;
  }

  let editing = false;

  const setEditMode = (value) => {
    editing = value;
    preview.style.display = value ? 'none' : 'block';
    input.style.display = value ? 'block' : 'none';
    editButton.textContent = value ? '✓' : '✎';
    editButton.setAttribute('aria-label', value ? 'Guardar descripción' : 'Editar descripción');
    if (value) {
      input.focus();
      input.setSelectionRange(input.value.length, input.value.length);
    }
  };

  const syncPreview = (text) => {
    const clean = (text || '').trim();
    if (!clean) {
      preview.textContent = 'Todavía no has guardado una descripción para la portada.';
      preview.classList.add('empty');
      return;
    }
    preview.textContent = clean;
    preview.classList.remove('empty');
  };

  try {
    const response = await apiRequest('home');
    const currentDescription = response.description || '';
    input.value = currentDescription;
    syncPreview(currentDescription);
    setEditMode(false);
  } catch (error) {
    status.textContent = error.message;
  }

  editButton.addEventListener('click', async () => {
    if (!editing) {
      setEditMode(true);
      return;
    }

    form.requestSubmit();
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const formData = new FormData();
    formData.append('description', input.value.trim());

    try {
      const response = await apiRequest('save-home', { method: 'POST', body: formData });
      syncPreview(input.value);
      status.textContent = response.message || 'Descripción guardada.';
      setEditMode(false);
    } catch (error) {
      status.textContent = error.message;
    }
  });
}

async function renderCalendarCards() {
  const grid = document.getElementById('calendarGrid');
  const rangeLabel = document.getElementById('calendarRange');

  if (!grid || !rangeLabel) {
    return;
  }

  rangeLabel.textContent = `Del ${formatShortDate(startDate)} al ${formatShortDate(endDate)}.`;

  let entriesByDate = new Map();
  try {
    const response = await apiRequest('list');
    entriesByDate = new Map((response.entries || []).map((entry) => [entry.date, entry]));
  } catch (error) {
    rangeLabel.textContent = error.message;
  }

  grid.innerHTML = '';
  const dates = getRangeDates();

  dates.forEach((date, index) => {
    const dateISO = formatDateISO(date);
    const entry = entriesByDate.get(dateISO) || null;
    const future = isFutureDate(date);

    const card = document.createElement('a');
    card.className = `day-card-tile${entry ? ' has-entry' : ''}${future ? ' locked' : ''}`;
    card.href = createDayLink(dateISO);
    card.style.setProperty('--delay', `${index * 45}ms`);

    const top = document.createElement('div');
    top.className = 'tile-top';
    top.innerHTML = `<strong>${formatLongDate(date)}</strong><span>${dateISO}</span>`;

    const body = document.createElement('div');
    body.className = 'tile-body';

    if (entry) {
      body.innerHTML = `
        <div class="tile-entry-image-wrap">${entry.photoUrl ? `<img src="${entry.photoUrl}" alt="Foto del día ${dateISO}" />` : '<div class="tile-image-empty">Sin imagen</div>'}</div>
        <h3>${entry.title ? entry.title : 'Sin título'}</h3>
        <p>${entry.description ? entry.description : 'Sin descripción.'}</p>
      `;
    } else if (future) {
      body.innerHTML = `
        <div class="tile-await">Aún no disponible</div>
        <p>Debes esperar a este día para poder capturar su información.</p>
      `;
    } else {
      body.innerHTML = `
        <div class="tile-pending">Pendiente</div>
        <p>Este día ya se puede llenar, pero todavía no tiene información guardada.</p>
      `;
    }

    const footer = document.createElement('div');
    footer.className = 'tile-footer';
    footer.textContent = entry ? 'Abrir registro guardado' : future ? 'Ver día bloqueado' : 'Llenar información';

    card.append(top, body, footer);
    grid.appendChild(card);
  });
}

async function renderDayPage() {
  const selectedDate = parseDateValue(document.body?.dataset?.selectedDate || '');
  if (!selectedDate) {
    window.location.href = 'index.php';
    return;
  }

  const dayStatus = document.getElementById('dayStatus');
  const dayTitle = document.getElementById('dayTitle');
  const daySubtitle = document.getElementById('daySubtitle');
  const savedView = document.getElementById('savedView');
  const savedImage = document.getElementById('savedImage');
  const savedPlaceholder = document.getElementById('savedPlaceholder');
  const savedTitle = document.getElementById('savedTitle');
  const savedDescription = document.getElementById('savedDescription');
  const editButton = document.getElementById('editButton');
  const entryForm = document.getElementById('entryForm');
  const photoInput = document.getElementById('photoInput');
  const titleInput = document.getElementById('titleInput');
  const descriptionInput = document.getElementById('descriptionInput');
  const cancelEditButton = document.getElementById('cancelEditButton');

  if (!dayStatus || !dayTitle || !daySubtitle || !savedView || !savedImage || !savedPlaceholder || !savedTitle || !savedDescription || !editButton || !entryForm || !photoInput || !titleInput || !descriptionInput || !cancelEditButton) {
    return;
  }

  const dateISO = formatDateISO(selectedDate);
  const withinRange = selectedDate >= startDate && selectedDate <= endDate;
  const future = isFutureDate(selectedDate);

  const setCollapsed = (element, collapsed) => {
    element.classList.toggle('is-collapsed', collapsed);
  };

  dayTitle.textContent = formatLongDate(selectedDate);
  daySubtitle.textContent = '';

  if (!withinRange) {
    dayStatus.textContent = 'Este día está fuera del rango permitido.';
    setCollapsed(entryForm, true);
    setCollapsed(savedView, false);
    editButton.style.display = 'none';
    return;
  }

  let currentEntry = null;

  try {
    const response = await apiRequest('read', { query: { date: dateISO } });
    currentEntry = response.entry;
  } catch (error) {
    dayStatus.textContent = error.message;
  }

  function paintSavedView(entry) {
    if (entry?.photoUrl) {
      savedImage.src = entry.photoUrl;
      savedImage.style.display = 'block';
      savedPlaceholder.style.display = 'none';
    } else {
      savedImage.removeAttribute('src');
      savedImage.style.display = 'none';
      savedPlaceholder.style.display = 'grid';
    }

    savedTitle.textContent = entry?.title?.trim() ? entry.title : 'Sin título';
    savedDescription.textContent = entry?.description?.trim() ? entry.description : 'Todavía no hay descripción guardada para este día.';
  }

  function toggleMode(editMode) {
    if (editMode) {
      setCollapsed(savedView, true);
      setCollapsed(entryForm, false);
      dayStatus.textContent = 'Modo edición activo. Cambia la información y guarda.';
      return;
    }

    setCollapsed(entryForm, true);
    setCollapsed(savedView, false);
    if (currentEntry) {
      dayStatus.textContent = '';
    } else if (future) {
      dayStatus.textContent = 'Debes esperar a este día para poder llenar la información.';
    } else {
      dayStatus.textContent = 'Este día todavía no tiene información. Puedes capturarla ahora.';
    }
  }

  paintSavedView(currentEntry);

  if (future) {
    setCollapsed(entryForm, true);
    setCollapsed(savedView, false);
    editButton.style.display = 'none';
    dayStatus.textContent = 'Debes esperar a este día para poder llenar la información.';
    return;
  }

  if (currentEntry) {
    titleInput.value = currentEntry.title || '';
    descriptionInput.value = currentEntry.description || '';
    toggleMode(false);
  } else {
    editButton.style.display = 'none';
    setCollapsed(entryForm, false);
    setCollapsed(savedView, true);
    dayStatus.textContent = 'Completa la información del día y guarda.';
  }

  editButton.addEventListener('click', () => {
    titleInput.value = currentEntry?.title || '';
    descriptionInput.value = currentEntry?.description || '';
    photoInput.value = '';
    toggleMode(true);
  });

  cancelEditButton.addEventListener('click', () => {
    if (currentEntry) {
      toggleMode(false);
    } else {
      titleInput.value = '';
      descriptionInput.value = '';
      photoInput.value = '';
    }
  });

  entryForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const formData = new FormData();
    formData.append('date', dateISO);
    formData.append('title', titleInput.value.trim());
    formData.append('description', descriptionInput.value.trim());

    const selectedFile = photoInput.files?.[0];
    if (selectedFile) {
      formData.append('photo', selectedFile);
    }

    try {
      const response = await apiRequest('save', { method: 'POST', body: formData });
      currentEntry = response.entry;
      paintSavedView(currentEntry);
      editButton.style.display = 'inline-flex';
      photoInput.value = '';
      toggleMode(false);
    } catch (error) {
      dayStatus.textContent = error.message;
    }
  });
}

if (isHomePage) {
  setupHomeDescription();
  renderCalendarCards();
}

if (isDayPage) {
  renderDayPage();
}
