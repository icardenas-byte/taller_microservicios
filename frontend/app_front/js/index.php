<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestor de Historias de Usuario</title>
  <link rel="stylesheet" href="../css/base.css">
</head>
<body>
  <header class="topbar">
    <div>
      <h1>Gestor de Historias de Usuario</h1>
      <p>Control de historias por sprint, responsable, estado y puntos.</p>
    </div>
    <button id="refreshBtn" class="icon-button" title="Actualizar datos">R</button>
  </header>
  <main class="layout">
    <section class="panel">
      <div class="section-title">
        <h2>Sprints</h2>
        <span id="sprintCount">0</span>
      </div>
      <form id="sprintForm" class="form">
        <input type="hidden" id="sprintId">
        <label>Nombre <input id="sprintNombre" required maxlength="100"></label>
        <label>Inicio <input id="sprintInicio" type="date" required></label>
        <label>Fin <input id="sprintFin" type="date" required></label>
        <div class="actions">
          <button type="submit">Guardar sprint</button>
          <button type="button" id="cancelSprint" class="secondary">Cancelar</button>
        </div>
      </form>
      <div id="sprintList" class="list"></div>
    </section>
    <section class="panel main-panel">
      <div class="section-title">
        <h2>Historias</h2>
        <span id="storyCount">0</span>
      </div>
      <form id="storyForm" class="form grid-form">
        <input type="hidden" id="storyId">
        <label>Titulo <input id="storyTitulo" required maxlength="150"></label>
        <label>Responsable <input id="storyResponsable" required maxlength="100"></label>
        <label>Sprint <select id="storySprint" required></select></label>
        <label>Estado
          <select id="storyEstado" required>
            <option value="nueva">Nueva</option>
            <option value="activa">Activa</option>
            <option value="finalizada">Finalizada</option>
            <option value="impedimento">Impedimento</option>
          </select>
        </label>
        <label>Puntos <input id="storyPuntos" type="number" min="1" required></label>
        <label>Creacion <input id="storyCreacion" type="date" required></label>
        <label>Finalizacion <input id="storyFinalizacion" type="date"></label>
        <label class="wide">Descripcion <textarea id="storyDescripcion" rows="3" required></textarea></label>
        <div class="actions wide">
          <button type="submit">Guardar historia</button>
          <button type="button" id="cancelStory" class="secondary">Cancelar</button>
        </div>
      </form>
      <div id="storiesBySprint" class="sprint-board"></div>
    </section>
    <section class="panel">
      <div class="section-title">
        <h2>Informe</h2>
        <select id="reportSprint"></select>
      </div>
      <div id="reportGeneral" class="metrics"></div>
      <div id="reportResponsables" class="table-wrap"></div>
    </section>
  </main>
  <div id="toast" class="toast"></div>
  <script src="/?asset=js"></script>
</body>
</html>
