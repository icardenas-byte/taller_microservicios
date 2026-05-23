const api = {
  sprints: '/api/sprints',
  historias: '/api/historias',
  agrupadas: '/api/historias/agrupadas',
  informes: '/api/informes'
};

const state = {
  sprints: [],
  historias: [],
  agrupadas: [],
  informe: null
};

const $ = (id) => document.getElementById(id);

async function request(url, options = {}) {
  const response = await fetch(url, {
    headers: { 'Content-Type': 'application/json' },
    ...options
  });
  const data = await response.json();
  if (!response.ok) {
    throw new Error(data.error || 'Ocurrio un error.');
  }
  return data;
}

async function loadAll() {
  state.sprints = await request(api.sprints);
  state.historias = await request(api.historias);
  state.agrupadas = await request(api.agrupadas);
  await loadReport();
  render();
}

async function loadReport() {
  const sprintId = $('reportSprint').value;
  const url = sprintId ? `${api.informes}?sprint_id=${sprintId}` : api.informes;
  state.informe = await request(url);
}

function render() {
  renderSprintOptions();
  renderSprints();
  renderStories();
  renderReport();
  $('sprintCount').textContent = state.sprints.length;
  $('storyCount').textContent = state.historias.length;
}

function renderSprintOptions() {
  const options = state.sprints
    .map((sprint) => `<option value="${sprint.id}">${escapeHtml(sprint.nombre)}</option>`)
    .join('');
  $('storySprint').innerHTML = `<option value="">Seleccione...</option>${options}`;

  const current = $('reportSprint').value;
  $('reportSprint').innerHTML = `<option value="">Todos</option>${options}`;
  $('reportSprint').value = current;
}

function renderSprints() {
  $('sprintList').innerHTML = state.sprints.map((sprint) => `
    <article class="item">
      <div class="item-header">
        <div>
          <h3>${escapeHtml(sprint.nombre)}</h3>
          <p>${sprint.fecha_inicio} a ${sprint.fecha_fin}</p>
        </div>
        <div class="small-actions">
          <button type="button" onclick="editSprint(${sprint.id})">Editar</button>
          <button type="button" class="danger" onclick="deleteSprint(${sprint.id})">Eliminar</button>
        </div>
      </div>
    </article>
  `).join('') || '<p>No hay sprints registrados.</p>';
}

function renderStories() {
  $('storiesBySprint').innerHTML = state.agrupadas.map((sprint) => `
    <article class="sprint-column">
      <h3>${escapeHtml(sprint.nombre)}</h3>
      ${sprint.historias.map(storyTemplate).join('') || '<p>Sin historias en este sprint.</p>'}
    </article>
  `).join('') || '<p>Crea un sprint para iniciar el tablero.</p>';
}

function storyTemplate(story) {
  return `
    <article class="story" data-state="${story.estado}">
      <div class="story-header">
        <div>
          <h3>${escapeHtml(story.titulo)}</h3>
          <p>${escapeHtml(story.descripcion)}</p>
          <span class="badge">${escapeHtml(story.responsable)} - ${story.estado} - ${story.puntos} pts</span>
        </div>
        <div class="small-actions">
          <button type="button" onclick="editStory(${story.id})">Editar</button>
          <button type="button" class="danger" onclick="deleteStory(${story.id})">Eliminar</button>
        </div>
      </div>
    </article>
  `;
}

function renderReport() {
  const general = state.informe?.general || {};
  const labels = {
    nueva: 'Nuevas',
    activa: 'Activas',
    finalizada: 'Finalizadas',
    impedimento: 'Con impedimento'
  };

  $('reportGeneral').innerHTML = Object.keys(labels).map((key) => `
    <div class="metric">
      <strong>${general[key]?.historias || 0}</strong>
      <span>${labels[key]} - ${general[key]?.puntos || 0} pts</span>
    </div>
  `).join('');

  const responsables = state.informe?.responsables || [];
  $('reportResponsables').innerHTML = `
    <table>
      <thead>
        <tr>
          <th>Responsable</th>
          <th>Nueva</th>
          <th>Activa</th>
          <th>Finalizada</th>
          <th>Impedimento</th>
          <th>Puntos</th>
        </tr>
      </thead>
      <tbody>
        ${responsables.map((row) => `
          <tr>
            <td>${escapeHtml(row.responsable)}</td>
            <td>${row.nueva}</td>
            <td>${row.activa}</td>
            <td>${row.finalizada}</td>
            <td>${row.impedimento}</td>
            <td>${row.puntos}</td>
          </tr>
        `).join('') || '<tr><td colspan="6">Sin datos para informar.</td></tr>'}
      </tbody>
    </table>
  `;
}

$('sprintForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  const id = $('sprintId').value;
  const payload = {
    nombre: $('sprintNombre').value,
    fecha_inicio: $('sprintInicio').value,
    fecha_fin: $('sprintFin').value
  };

  try {
    await request(id ? `${api.sprints}/${id}` : api.sprints, {
      method: id ? 'PUT' : 'POST',
      body: JSON.stringify(payload)
    });
    resetSprintForm();
    await loadAll();
    toast('Sprint guardado.');
  } catch (error) {
    toast(error.message);
  }
});

$('storyForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  const id = $('storyId').value;
  const payload = {
    titulo: $('storyTitulo').value,
    descripcion: $('storyDescripcion').value,
    responsable: $('storyResponsable').value,
    estado: $('storyEstado').value,
    puntos: $('storyPuntos').value,
    fecha_creacion: $('storyCreacion').value,
    fecha_finalizacion: $('storyFinalizacion').value,
    sprint_id: $('storySprint').value
  };

  try {
    await request(id ? `${api.historias}/${id}` : api.historias, {
      method: id ? 'PUT' : 'POST',
      body: JSON.stringify(payload)
    });
    resetStoryForm();
    await loadAll();
    toast('Historia guardada.');
  } catch (error) {
    toast(error.message);
  }
});

$('cancelSprint').addEventListener('click', resetSprintForm);
$('cancelStory').addEventListener('click', resetStoryForm);
$('refreshBtn').addEventListener('click', loadAll);
$('reportSprint').addEventListener('change', async () => {
  await loadReport();
  renderReport();
});

function editSprint(id) {
  const sprint = state.sprints.find((item) => Number(item.id) === Number(id));
  if (!sprint) return;
  $('sprintId').value = sprint.id;
  $('sprintNombre').value = sprint.nombre;
  $('sprintInicio').value = sprint.fecha_inicio;
  $('sprintFin').value = sprint.fecha_fin;
}

async function deleteSprint(id) {
  if (!confirm('Eliminar este sprint y sus historias?')) return;
  await request(`${api.sprints}/${id}`, { method: 'DELETE' });
  await loadAll();
  toast('Sprint eliminado.');
}

function editStory(id) {
  const story = state.historias.find((item) => Number(item.id) === Number(id));
  if (!story) return;
  $('storyId').value = story.id;
  $('storyTitulo').value = story.titulo;
  $('storyDescripcion').value = story.descripcion;
  $('storyResponsable').value = story.responsable;
  $('storyEstado').value = story.estado;
  $('storyPuntos').value = story.puntos;
  $('storyCreacion').value = story.fecha_creacion;
  $('storyFinalizacion').value = story.fecha_finalizacion || '';
  $('storySprint').value = story.sprint_id;
}

async function deleteStory(id) {
  if (!confirm('Eliminar esta historia?')) return;
  await request(`${api.historias}/${id}`, { method: 'DELETE' });
  await loadAll();
  toast('Historia eliminada.');
}

function resetSprintForm() {
  $('sprintForm').reset();
  $('sprintId').value = '';
}

function resetStoryForm() {
  $('storyForm').reset();
  $('storyId').value = '';
  $('storyCreacion').value = new Date().toISOString().slice(0, 10);
}

function toast(message) {
  $('toast').textContent = message;
  $('toast').classList.add('show');
  setTimeout(() => $('toast').classList.remove('show'), 2400);
}

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

resetStoryForm();
loadAll().catch((error) => toast(error.message));
