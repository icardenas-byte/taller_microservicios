const API = Object.freeze({
  SPRINTS: '/api/sprints',
  HISTORIAS: '/api/historias',
  AGRUPADAS: '/api/historias/agrupadas',
  INFORMES: '/api/informes',
});

const ESTADOS = Object.freeze({
  NUEVA: 'nueva',
  ACTIVA: 'activa',
  FINALIZADA: 'finalizada',
  IMPEDIMENTO: 'impedimento',
});

const ETIQUETAS_ESTADO = Object.freeze({
  [ESTADOS.NUEVA]: 'Nuevas',
  [ESTADOS.ACTIVA]: 'Activas',
  [ESTADOS.FINALIZADA]: 'Finalizadas',
  [ESTADOS.IMPEDIMENTO]: 'Con impedimento',
});

const SELECTORES = Object.freeze({
  SPRINT_ID: 'sprint-id',
  SPRINT_NOMBRE: 'sprint-nombre',
  SPRINT_INICIO: 'sprint-inicio',
  SPRINT_FIN: 'sprint-fin',
  HISTORIA_ID: 'historia-id',
  HISTORIA_TITULO: 'historia-titulo',
  HISTORIA_DESCRIPCION: 'historia-descripcion',
  HISTORIA_RESPONSABLE: 'historia-responsable',
  HISTORIA_ESTADO: 'historia-estado',
  HISTORIA_PUNTOS: 'historia-puntos',
  HISTORIA_CREACION: 'historia-creacion',
  HISTORIA_FINALIZACION: 'historia-finalizacion',
  HISTORIA_SPRINT: 'historia-sprint',
  FORMULARIO_SPRINT: 'formulario-sprint',
  FORMULARIO_HISTORIA: 'formulario-historia',
  LISTA_SPRINTS: 'lista-sprints',
  HISTORIAS_POR_SPRINT: 'historias-por-sprint',
  CONTADOR_SPRINTS: 'contador-sprints',
  CONTADOR_HISTORIAS: 'contador-historias',
  INFORME_SPRINT: 'informe-sprint',
  INFORME_GENERAL: 'informe-general',
  INFORME_RESPONSABLES: 'informe-responsables',
  TOAST: 'toast',
  BTN_ACTUALIZAR: 'btn-actualizar',
  BTN_CANCELAR_SPRINT: 'btn-cancelar-sprint',
  BTN_CANCELAR_HISTORIA: 'btn-cancelar-historia',
});

const state = {
  sprints: [],
  historias: [],
  agrupadas: [],
  informe: null,
};

const $ = (id) => document.getElementById(id);

const obtenerElemento = (selector) => {
  const elemento = $(selector);
  if (!elemento) {
    throw new Error(`Elemento no encontrado: #${selector}`);
  }
  return elemento;
};

async function peticion(url, opciones = {}) {
  const respuesta = await fetch(url, {
    headers: { 'Content-Type': 'application/json' },
    ...opciones,
  });

  const datos = await respuesta.json();

  if (!respuesta.ok) {
    const mensaje = datos.error || datos.errors?.join(', ') || 'Ocurrió un error.';
    throw new Error(mensaje);
  }

  return datos;
}

async function cargarTodo() {
  const [sprints, historias, agrupadas] = await Promise.all([
    peticion(API.SPRINTS),
    peticion(API.HISTORIAS),
    peticion(API.AGRUPADAS),
  ]);

  state.sprints = sprints;
  state.historias = historias;
  state.agrupadas = agrupadas;
  await cargarInforme();
  renderizar();
}

async function cargarInforme() {
  const sprintId = obtenerElemento(SELECTORES.INFORME_SPRINT).value;
  const url = sprintId
    ? `${API.INFORMES}?sprint_id=${encodeURIComponent(sprintId)}`
    : API.INFORMES;

  state.informe = await peticion(url);
}

function renderizar() {
  renderizarOpcionesSprints();
  renderizarSprints();
  renderizarHistorias();
  renderizarInforme();
  actualizarContadores();
}

function actualizarContadores() {
  obtenerElemento(SELECTORES.CONTADOR_SPRINTS).textContent = state.sprints.length;
  obtenerElemento(SELECTORES.CONTADOR_HISTORIAS).textContent = state.historias.length;
}

function renderizarOpcionesSprints() {
  const opciones = state.sprints
    .map((sprint) => `<option value="${sprint.id}">${escaparHtml(sprint.nombre)}</option>`)
    .join('');

  const selectorHistoria = obtenerElemento(SELECTORES.HISTORIA_SPRINT);
  selectorHistoria.innerHTML = `<option value="" disabled selected>Seleccione sprint...</option>${opciones}`;

  const selectorInforme = obtenerElemento(SELECTORES.INFORME_SPRINT);
  const valorActual = selectorInforme.value;

  selectorInforme.innerHTML = `<option value="">Todos los sprints</option>${opciones}`;
  selectorInforme.value = valorActual;
}

function renderizarSprints() {
  const contenedor = obtenerElemento(SELECTORES.LISTA_SPRINTS);

  if (state.sprints.length === 0) {
    contenedor.innerHTML = '<p class="empty">No hay sprints registrados.</p>';
    return;
  }

  contenedor.innerHTML = state.sprints.map((sprint) => `
    <article class="item" data-id="${sprint.id}">
      <div class="item-header">
        <div>
          <h3>${escaparHtml(sprint.nombre)}</h3>
          <time datetime="${sprint.fecha_inicio}">${formatearFecha(sprint.fecha_inicio)}</time>
          <span> a </span>
          <time datetime="${sprint.fecha_fin}">${formatearFecha(sprint.fecha_fin)}</time>
        </div>
        <div class="small-actions">
          <button type="button" data-accion="editar-sprint" data-id="${sprint.id}">Editar</button>
          <button type="button" class="danger" data-accion="eliminar-sprint" data-id="${sprint.id}">Eliminar</button>
        </div>
      </div>
    </article>
  `).join('');
}

function renderizarHistorias() {
  const contenedor = obtenerElemento(SELECTORES.HISTORIAS_POR_SPRINT);

  if (state.agrupadas.length === 0) {
    contenedor.innerHTML = '<p class="empty">Crea un sprint para iniciar el tablero.</p>';
    return;
  }

  contenedor.innerHTML = state.agrupadas.map((sprint) => `
    <section class="sprint-column" data-sprint-id="${sprint.id}">
      <h3>${escaparHtml(sprint.nombre)}</h3>
      ${sprint.historias.length > 0
        ? sprint.historias.map(plantillaHistoria).join('')
        : '<p class="empty">Sin historias en este sprint.</p>'}
    </section>
  `).join('');
}

function plantillaHistoria(historia) {
  return `
    <article class="story" data-state="${escaparHtml(historia.estado)}" data-id="${historia.id}">
      <div class="story-header">
        <div>
          <h3>${escaparHtml(historia.titulo)}</h3>
          <p>${escaparHtml(historia.descripcion)}</p>
          <span class="badge">
            ${escaparHtml(historia.responsable)} · ${escaparHtml(historia.estado)} · ${historia.puntos} pts
          </span>
        </div>
        <div class="small-actions">
          <button type="button" data-accion="editar-historia" data-id="${historia.id}">Editar</button>
          <button type="button" class="danger" data-accion="eliminar-historia" data-id="${historia.id}">Eliminar</button>
        </div>
      </div>
    </article>
  `;
}

function renderizarInforme() {
  renderizarMetricasGenerales();
  renderizarTablaResponsables();
}

function renderizarMetricasGenerales() {
  const general = state.informe?.general ?? {};
  const contenedor = obtenerElemento(SELECTORES.INFORME_GENERAL);

  contenedor.innerHTML = Object.values(ESTADOS).map((estado) => `
    <div class="metric" data-estado="${estado}">
      <strong>${general[estado]?.historias ?? 0}</strong>
      <span>${ETIQUETAS_ESTADO[estado]} · ${general[estado]?.puntos ?? 0} pts</span>
    </div>
  `).join('');
}

function renderizarTablaResponsables() {
  const responsables = state.informe?.responsables ?? [];
  const contenedor = obtenerElemento(SELECTORES.INFORME_RESPONSABLES);

  if (responsables.length === 0) {
    contenedor.innerHTML = '<p class="empty">Sin datos para informar.</p>';
    return;
  }

  contenedor.innerHTML = `
    <table>
      <thead>
        <tr>
          <th scope="col">Responsable</th>
          <th scope="col">Nueva</th>
          <th scope="col">Activa</th>
          <th scope="col">Finalizada</th>
          <th scope="col">Impedimento</th>
          <th scope="col">Puntos</th>
        </tr>
      </thead>
      <tbody>
        ${responsables.map((fila) => `
          <tr>
            <td>${escaparHtml(fila.responsable)}</td>
            <td>${fila.nueva ?? 0}</td>
            <td>${fila.activa ?? 0}</td>
            <td>${fila.finalizada ?? 0}</td>
            <td>${fila.impedimento ?? 0}</td>
            <td><strong>${fila.puntos ?? 0}</strong></td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  `;
}

function inicializarEventos() {
  document.addEventListener('click', manejarClickDelegado);

  obtenerElemento(SELECTORES.FORMULARIO_SPRINT).addEventListener('submit', manejarEnvioSprint);
  obtenerElemento(SELECTORES.FORMULARIO_HISTORIA).addEventListener('submit', manejarEnvioHistoria);

  obtenerElemento(SELECTORES.BTN_CANCELAR_SPRINT).addEventListener('click', reiniciarFormularioSprint);
  obtenerElemento(SELECTORES.BTN_CANCELAR_HISTORIA).addEventListener('click', reiniciarFormularioHistoria);

  obtenerElemento(SELECTORES.BTN_ACTUALIZAR).addEventListener('click', cargarTodo);
  obtenerElemento(SELECTORES.INFORME_SPRINT).addEventListener('change', async () => {
    await cargarInforme();
    renderizarInforme();
  });
}

function manejarClickDelegado(evento) {
  const boton = evento.target.closest('button[data-accion]');
  if (!boton) return;

  const accion = boton.dataset.accion;
  const id = Number(boton.dataset.id);

  switch (accion) {
    case 'editar-sprint':
      editarSprint(id);
      break;
    case 'eliminar-sprint':
      eliminarSprint(id);
      break;
    case 'editar-historia':
      editarHistoria(id);
      break;
    case 'eliminar-historia':
      eliminarHistoria(id);
      break;
  }
}

async function manejarEnvioSprint(evento) {
  evento.preventDefault();

  const id = obtenerElemento(SELECTORES.SPRINT_ID).value;
  const payload = {
    nombre: obtenerElemento(SELECTORES.SPRINT_NOMBRE).value.trim(),
    fecha_inicio: obtenerElemento(SELECTORES.SPRINT_INICIO).value,
    fecha_fin: obtenerElemento(SELECTORES.SPRINT_FIN).value,
  };

  try {
    await peticion(id ? `${API.SPRINTS}/${id}` : API.SPRINTS, {
      method: id ? 'PUT' : 'POST',
      body: JSON.stringify(payload),
    });

    reiniciarFormularioSprint();
    await cargarTodo();
    mostrarToast('Sprint guardado correctamente.');
  } catch (error) {
    mostrarToast(error.message, 'error');
  }
}

async function manejarEnvioHistoria(evento) {
  evento.preventDefault();

  const id = obtenerElemento(SELECTORES.HISTORIA_ID).value;
  const payload = {
    titulo: obtenerElemento(SELECTORES.HISTORIA_TITULO).value.trim(),
    descripcion: obtenerElemento(SELECTORES.HISTORIA_DESCRIPCION).value.trim(),
    responsable: obtenerElemento(SELECTORES.HISTORIA_RESPONSABLE).value.trim(),
    estado: obtenerElemento(SELECTORES.HISTORIA_ESTADO).value,
    puntos: Number(obtenerElemento(SELECTORES.HISTORIA_PUNTOS).value),
    fecha_creacion: obtenerElemento(SELECTORES.HISTORIA_CREACION).value,
    fecha_finalizacion: obtenerElemento(SELECTORES.HISTORIA_FINALIZACION).value || null,
    sprint_id: Number(obtenerElemento(SELECTORES.HISTORIA_SPRINT).value),
  };

  try {
    await peticion(id ? `${API.HISTORIAS}/${id}` : API.HISTORIAS, {
      method: id ? 'PUT' : 'POST',
      body: JSON.stringify(payload),
    });

    reiniciarFormularioHistoria();
    await cargarTodo();
    mostrarToast('Historia guardada correctamente.');
  } catch (error) {
    mostrarToast(error.message, 'error');
  }
}

function editarSprint(id) {
  const sprint = state.sprints.find((item) => item.id === id);
  if (!sprint) {
    mostrarToast('Sprint no encontrado.', 'error');
    return;
  }

  obtenerElemento(SELECTORES.SPRINT_ID).value = sprint.id;
  obtenerElemento(SELECTORES.SPRINT_NOMBRE).value = sprint.nombre;
  obtenerElemento(SELECTORES.SPRINT_INICIO).value = sprint.fecha_inicio;
  obtenerElemento(SELECTORES.SPRINT_FIN).value = sprint.fecha_fin;
}

async function eliminarSprint(id) {
  const sprint = state.sprints.find((item) => item.id === id);
  if (!sprint) return;

  const confirmado = await confirmar(`¿Eliminar el sprint "${sprint.nombre}" y todas sus historias?`);
  if (!confirmado) return;

  try {
    await peticion(`${API.SPRINTS}/${id}`, { method: 'DELETE' });
    await cargarTodo();
    mostrarToast('Sprint eliminado correctamente.');
  } catch (error) {
    mostrarToast(error.message, 'error');
  }
}

function editarHistoria(id) {
  const historia = state.historias.find((item) => item.id === id);
  if (!historia) {
    mostrarToast('Historia no encontrada.', 'error');
    return;
  }

  obtenerElemento(SELECTORES.HISTORIA_ID).value = historia.id;
  obtenerElemento(SELECTORES.HISTORIA_TITULO).value = historia.titulo;
  obtenerElemento(SELECTORES.HISTORIA_DESCRIPCION).value = historia.descripcion;
  obtenerElemento(SELECTORES.HISTORIA_RESPONSABLE).value = historia.responsable;
  obtenerElemento(SELECTORES.HISTORIA_ESTADO).value = historia.estado;
  obtenerElemento(SELECTORES.HISTORIA_PUNTOS).value = historia.puntos;
  obtenerElemento(SELECTORES.HISTORIA_CREACION).value = historia.fecha_creacion;
  obtenerElemento(SELECTORES.HISTORIA_FINALIZACION).value = historia.fecha_finalizacion ?? '';
  obtenerElemento(SELECTORES.HISTORIA_SPRINT).value = historia.sprint_id;
}

async function eliminarHistoria(id) {
  const historia = state.historias.find((item) => item.id === id);
  if (!historia) return;

  const confirmado = await confirmar(`¿Eliminar la historia "${historia.titulo}"?`);
  if (!confirmado) return;

  try {
    await peticion(`${API.HISTORIAS}/${id}`, { method: 'DELETE' });
    await cargarTodo();
    mostrarToast('Historia eliminada correctamente.');
  } catch (error) {
    mostrarToast(error.message, 'error');
  }
}

function reiniciarFormularioSprint() {
  obtenerElemento(SELECTORES.FORMULARIO_SPRINT).reset();
  obtenerElemento(SELECTORES.SPRINT_ID).value = '';
}

function reiniciarFormularioHistoria() {
  obtenerElemento(SELECTORES.FORMULARIO_HISTORIA).reset();
  obtenerElemento(SELECTORES.HISTORIA_ID).value = '';
  obtenerElemento(SELECTORES.HISTORIA_CREACION).value = new Date().toISOString().slice(0, 10);
}

function escaparHtml(valor) {
  const div = document.createElement('div');
  div.textContent = String(valor ?? '');
  return div.innerHTML;
}

function formatearFecha(fechaIso) {
  if (!fechaIso) return '';
  const [anio, mes, dia] = fechaIso.split('-');
  return `${dia}/${mes}/${anio}`;
}

function mostrarToast(mensaje, tipo = 'success') {
  const toast = obtenerElemento(SELECTORES.TOAST);
  toast.textContent = mensaje;
  toast.className = `toast show ${tipo}`;

  setTimeout(() => {
    toast.classList.remove('show');
  }, 3000);
}

async function confirmar(mensaje) {
  return window.confirm(mensaje);
}

function inicializar() {
  reiniciarFormularioHistoria();
  inicializarEventos();
  cargarTodo().catch((error) => mostrarToast(error.message, 'error'));
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', inicializar);
} else {
  inicializar();
}