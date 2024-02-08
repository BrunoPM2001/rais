<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid miclase">
    <a class="navbar-brand" href="#">RAIS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Estudios</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Gestión de convocatorias</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="#">Gestión de grupos</a></li>
            <li><a class="dropdown-item" href="#">Gestión de proyectos de grupos</a></li>
            <li><a class="dropdown-item" href="#">Gestión de proyectos FEX</a></li>
            <li><a class="dropdown-item" href="#">Gestión de proyectos</a></li>
            <li><a class="dropdown-item" href="#">Informes técnicos</a></li>
            <li><a class="dropdown-item" href="#">Monitoreo</a></li>
            <li><a class="dropdown-item" href="#">Deuda de proyectos</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="#">Gestión de publicaciones</a></li>
            <li><a class="dropdown-item" href="#">Sincronizar publicaciones</a></li>
            <li><a class="dropdown-item" href="#">Revistas</a></li>
            <li><a class="dropdown-item" href="#">Gestión de laboratorios</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="#">Gestión de investigadores</a></li>
            <li><a class="dropdown-item" href="#">Docente investigador</a></li>
            <li><a class="dropdown-item" href="#">Gestión de RRHH</a></li>
            <li><a class="dropdown-item" href="#">Gestión de SUM</a></li>
            <li><a class="dropdown-item" href="#">Gestión de Resoluciones</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Economía</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Asignaciones a proyectos</a></li>
            <li><a class="dropdown-item" href="#">Incentivos a docentes</a></li>
            <li><a class="dropdown-item" href="#">Deuda proyectos</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="#">Reporte de asignaciones</a></li>
            <li><a class="dropdown-item" href="#">Reporte de incentivos por docente</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="#">Formatos R.E.</a></li>
            <li><a class="dropdown-item" href="#">Formato planilla de incentivo</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="#">Gestión de comprobantes</a></li>
            <li><a class="dropdown-item" href="#">Gestión de transferencias</a></li>
            <li><a class="dropdown-item" href="#">Gestión cierre de rendición</a></li>
            <li><a class="dropdown-item" href="#">Subvención de Publicación</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Reportes</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Reporte por estudio</a></li>
            <li><a class="dropdown-item" href="#">Reporte por grupo</a></li>
            <li><a class="dropdown-item" href="#">Reporte por proyecto</a></li>
            <li><a class="dropdown-item" href="#">Reporte por docente</a></li>
            <li><a class="dropdown-item" href="#">Consolidado general</a></li>
            <li><a class="dropdown-item" href="#">Consolidado tesis</a></li>
            <li><a class="dropdown-item" href="#">Reporte de presupuesto</a></li>
            <li><a class="dropdown-item" href="#">Reporte de deudores</a></li>
            <li><a class="dropdown-item" href="#">Lista de deudores</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="#">Reporte de publicaciones</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Constancias</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Facultad</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('view_facultad_convocatorias') }}">Convocatorias</a></li>
            <li>
              <hr class="dropdown-dividir">
            </li>
            <li><a class="dropdown-item" href="#">Usuarios facultad</a></li>
            <li><a class="dropdown-item" href="#">Usuarios evaluadores</a></li>
            <li><a class="dropdown-item" href="#">Asignación de evaluadores</a></li>
            <li><a class="dropdown-item" href="#">Proyectos evaluados</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Administrador</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('view_lineas') }}">Lineas de investigación</a></li>
            <li><a class="dropdown-item" href="{{ route('view_dependencias') }}">Dependencias</a></li>
            <li>
              <hr class="dropdown-dividir">
            </li>
            <li><a class="dropdown-item" href="{{ route('view_usuariosAdmin') }}">Usuarios administrativos</a></li>
            <li><a class="dropdown-item" href="#">Usuarios investigadores</a></li>
            <li><a class="dropdown-item" href="#">Usuarios evaluadores</a></li>
            <li><a class="dropdown-item" href="#">Usuarios docentes</a></li>
          </ul>
        </li>
      </ul>
      <div class="dropdown text-end">
        <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="https://github.com/mdo.png" alt="mdo" class="rounded-circle" width="32" height="32">
        </a>
        <ul class="dropdown-menu dropdown-menu-end text-small">
          <li>
            <span class="dropdown-item">Max Ichajaya</span>
            <span class="dropdown-item">max.ichajaya@unmsm.edu.pe</span>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li><a class="dropdown-item" href="#">Cambiar contraseña</a></li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li><a class="dropdown-item" href="#">Sign out</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>