<nav class="bg-white border-gray-200 dark:border-gray-600 dark:bg-gray-900">
  <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl p-4">
    <a href="https://flowbite.com" class="flex items-center space-x-3 rtl:space-x-reverse">
      <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">RAIS</span>
    </a>
    <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
      <button type="button" class="flex text-sm bg-gray-800 rounded-full md:me-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600" id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown" data-dropdown-placement="bottom">
        <span class="sr-only">Abrir menu de usuario</span>
        <img class="w-8 h-8 rounded-full" src="/docs/images/people/profile-picture-3.jpg" alt="user photo">
      </button>
      <!-- Dropdown menu -->
      <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow dark:bg-gray-700 dark:divide-gray-600" id="user-dropdown">
        <div class="px-4 py-3">
          <span class="block text-sm text-gray-900 dark:text-white">Max Ichajaya</span>
          <span class="block text-sm  text-gray-500 truncate dark:text-gray-400">max.ichajaya@unmsm.edu.pe</span>
        </div>
        <ul class="py-2" aria-labelledby="user-menu-button">
          <li>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Cambiar contraseña</a>
          </li>
          <li>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Cerrar sesión</a>
          </li>
        </ul>
      </div>
      <button data-collapse-toggle="mega-menu-full" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600" aria-controls="mega-menu-full" aria-expanded="false">
        <span class="sr-only">Abrir menu</span>
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15" />
        </svg>
      </button>
    </div>
    <div id="mega-menu-full" class="items-center justify-between font-medium hidden w-full md:flex md:w-auto md:order-1">
      <ul class="flex flex-col p-2 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
        <li>
          <button id="submenu-button-1" data-collapse-toggle="submenu-1" class="flex items-center justify-between w-full py-2 px-3 text-gray-900 rounded md:w-auto hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-600 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-blue-500 md:dark:hover:bg-transparent dark:border-gray-700">
            Estudios
            <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
            </svg>
          </button>
        </li>
        <li>
          <button id="submenu-button-2" data-collapse-toggle="submenu-2" class="flex items-center justify-between w-full py-2 px-3 text-gray-900 rounded md:w-auto hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-600 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-blue-500 md:dark:hover:bg-transparent dark:border-gray-700">
            Economía
            <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
            </svg>
          </button>
        </li>
        <li>
          <button id="submenu-button-3" data-collapse-toggle="submenu-3" class="flex items-center justify-between w-full py-2 px-3 text-gray-900 rounded md:w-auto hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-600 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-blue-500 md:dark:hover:bg-transparent dark:border-gray-700">
            Reportes
            <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
            </svg>
          </button>
        </li>
        <li>
          <button id="submenu-button-4" class="flex items-center justify-between w-full py-2 px-3 text-gray-900 rounded md:w-auto hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-600 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-blue-500 md:dark:hover:bg-transparent dark:border-gray-700">
            Constancias
          </button>
        </li>
        <li>
          <button id="submenu-button-5" data-collapse-toggle="submenu-5" class="flex items-center justify-between w-full py-2 px-3 text-gray-900 rounded md:w-auto hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-600 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-blue-500 md:dark:hover:bg-transparent dark:border-gray-700">
            Facultad
            <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
            </svg>
          </button>
        </li>
        <li>
          <button id="submenu-button-6" data-collapse-toggle="submenu-6" class="flex items-center justify-between w-full py-2 px-3 text-gray-900 rounded md:w-auto hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-600 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-blue-500 md:dark:hover:bg-transparent dark:border-gray-700">
            Admin
            <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
            </svg>
          </button>
        </li>
      </ul>
    </div>
  </div>
  <div id="submenu-1" class="mt-1 border-gray-200 shadow-sm bg-gray-50 md:bg-white border-y dark:bg-gray-800 dark:border-gray-600 hidden">
    <div class="grid max-w-screen-xl px-4 py-5 mx-auto text-gray-900 dark:text-white sm:grid-cols-4 md:px-6">
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de convocatorias</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Convocatorias habilitades para el periodo actual.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de grupos</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Visualización de los grupos de investigación existentes
              y de las solicitudes para creación de grupos.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de proyectos de grupos</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Proyectos presentados por grupos de investigación.
            </span>
          </a>
        </li>
      </ul>
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de proyectos FEX</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Proyectos con financiamiento externo.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de proyectos</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Proyectos generales presentados al VRIP.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Informes técnicos</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Informes presentados por los integrantes de los proyectos.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Monitoreo</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Listado de proyectos y de las metas a alcanzar.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Deuda de proyectos</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Historial de deudas de los integrantes de proyectos.
            </span>
          </a>
        </li>
      </ul>
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de publicaciones</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Publicaciones de diferentes tipos hechas por autores.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Sincronizar publicaciones</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Publicaciones enviadas a CYBERTESIS u ORCID.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Revistas</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Listado de revistas de la universidad.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de laboratorios</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Listado de laboratorios de la universidad.
            </span>
          </a>
        </li>
      </ul>
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de investigadores</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Registro de investigadores del RAIS.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Docente investigador</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Perfiles de docentes registrados como investigador
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de RRHH</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Acceso a data de RRRHH a través de una vista.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de SUM</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Acceso a data del SUM a través de una vista.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de Resoluciones</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Resoluciones registradas para el sistema
            </span>
          </a>
        </li>
      </ul>
    </div>
  </div>
  <div id="submenu-2" class="mt-1 border-gray-200 shadow-sm bg-gray-50 md:bg-white border-y dark:bg-gray-800 dark:border-gray-600 hidden">
    <div class="grid max-w-screen-xl px-4 py-5 mx-auto text-gray-900 dark:text-white sm:grid-cols-3 md:px-6">
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Asignaciones a proyectos</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Asignación de presupuesto para proyectos.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Incentivo a docentes</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Listado de incentivos para investigadores.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Deuda proyectos</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Deudas registradas por proyectos pasados.
            </span>
          </a>
        </li>
      </ul>
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Reporte de asignaciones</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Generación de reportes en PDF por proyecto.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Reporte de incentivos por docentes</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Generación de PDF con el listado de incentivos para docentes.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Formatos RE</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Reporte en base a facultad, tipo de proyecto y n° de asignación.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Formato planilla de incentivo</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Generación del reporte de planilla para incentivos.
            </span>
          </a>
        </li>
      </ul>
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de comprobantes</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Comprobantes cargados por miembros de proyectos.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de transferencias</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Transferencias hechas para la justificación de presupuesto
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión cierre de rendición</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Rendición de cuentas para presupuesto.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Subvención de publicaciones</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Subvención de publicaciones por docente y publicación.
            </span>
          </a>
        </li>
      </ul>
    </div>
  </div>
  <div id="submenu-3" class="mt-1 border-gray-200 shadow-sm bg-gray-50 md:bg-white border-y dark:bg-gray-800 dark:border-gray-600 hidden">
    <div class="grid max-w-screen-xl px-4 py-5 mx-auto text-gray-900 dark:text-white sm:grid-cols-3 md:px-6">
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Reporte por estudio</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Reporte de proyectos del 2017 hacia atrás.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Reporte por grupo</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Reporte de grupo de investigación y miembros.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Reporte por proyecto</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Reporte de proyectos del 2017 en adelante.
            </span>
          </a>
        </li>
      </ul>
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Reporte por docente</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Listado de proyectos en los que intervino un docente.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Consolidado general</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Reporte de proyectos por facultad según tipos.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Consolidado Tesis</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Reporte de la cantidad de tesis por facultad según financiamiento.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Reporte de presupuesto</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Reporte del presupuesto asignado a proyectos.
            </span>
          </a>
        </li>
      </ul>
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Reporte de deudores</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Reporte de deudores por facultad en proyectos.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Listado de deudores</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Listado de deudores por facultad en proyectos
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Reporte de publicaciones</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Publicaciones hechas por un docente.
            </span>
          </a>
        </li>
      </ul>
    </div>
  </div>
  <div id="submenu-5" class="mt-1 border-gray-200 shadow-sm bg-gray-50 md:bg-white border-y dark:bg-gray-800 dark:border-gray-600 hidden">
    <div class="grid max-w-screen-xl px-4 py-5 mx-auto text-gray-900 dark:text-white sm:grid-cols-2 md:px-6">
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Convocatorias</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Convocatorias de proyectos hechas hasta el 2016.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Usuarios facultad</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Gestión de usuarios de las facultades.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Usuarios evaluadores</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Gestión de usuarios con perfil de evaluador.
            </span>
          </a>
        </li>
      </ul>
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Asignación de evaluadores</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Asignar evaluadores a proyectos pendientes de evaluación.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Proyectos evaluados</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Listado de proyectos y sus evaluadores correspondientes.
            </span>
          </a>
        </li>
      </ul>
    </div>
  </div>
  <div id="submenu-6" class="mt-1 border-gray-200 shadow-sm bg-gray-50 md:bg-white border-y dark:bg-gray-800 dark:border-gray-600 hidden">
    <div class="grid max-w-screen-xl px-4 py-5 mx-auto text-gray-900 dark:text-white sm:grid-cols-2 md:px-6">
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Lineas de investigación</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Gestión de las lineas de investigación registradas.
            </span>
          </a>
        </li>
        <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
          <div class="font-semibold">Facultades</div>
          <span class="text-sm text-gray-500 dark:text-gray-400">
            Gestión de facultades.
          </span>
        </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Gestión de usuarios</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Gestión de todos los tipos de usuarios del Rais.
            </span>
          </a>
        </li>
        <li>
      </ul>
      <ul>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Dependencias</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Gestión de las dependencias de facultades y de la UNMSM.
            </span>
          </a>
        </li>
        <li>
          <a href="#" class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="font-semibold">Institutos</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              Gestión de institutos registrados por facultades.
            </span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>