<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lineas de investigación</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body>
  @include('admin.components.navbar')
  <hr>
  <div class="mx-auto max-w-screen-xl p-4">
    <!--  Tabs  -->
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
      <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="default-tab" data-tabs-toggle="#default-tab-content" role="tablist">
        <li class="me-2" role="presentation">
          <button class="inline-block p-4 border-b-2 rounded-t-lg" id="listado-tab" data-tabs-target="#tab_listado" type="button" role="tab" aria-controls="tab_listado" aria-selected="false">Lista de lineas</button>
        </li>
        <li class="me-2" role="presentation">
          <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="crear-tab" data-tabs-target="#tab_crear" type="button" role="tab" aria-controls="tab_crear" aria-selected="false">Crear linea nueva</button>
        </li>
      </ul>
    </div>
    <div id="default-tab-content">
      <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="tab_listado" role="tabpanel" aria-labelledby="profile-tab">
        <!--  Seleccionar facultad  -->
        <form class="max-w-sm mx-auto">
          <div class="mb-5">
            <label for="facultad" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Facultad:</label>
            <select id="facultad" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
              <option value="null" selected>Ninguna</option>
              @foreach($facultades as $facultad)
              <option value="{{ $facultad->id }}">{{ $facultad->nombre }}</option>
              @endforeach
            </select>
          </div>
        </form>
        <!--  Tabla de lineas de investigación  -->
        <div class="relative p-4 overflow-x-auto shadow-md sm:rounded-lg">
          <table id="lineas_table" style="width:100%">
            <thead>
              <tr>
                <th>
                </th>
                <th>
                  Código
                </th>
                <th>
                  Linea de investigación
                </th>
                <th>
                  Resolución
                </th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>

        </div>
      </div>
      <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="tab_crear" role="tabpanel" aria-labelledby="dashboard-tab">
        <!--  Crear nueva linea de investigación  -->
        <form action="{{ route('create_linea') }}" method="post" class="max-w-md mx-auto">
          @csrf
          <div class="mb-5">
            <label for="create_facultad" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Facultad:</label>
            <select id="create_facultad" name="facultad_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
              <option value="">Ninguna</option>
              @foreach($facultades as $facultad)
              <option value="{{ $facultad->id }}">{{ $facultad->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-5">
            <label for="create_padre" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Padre:</label>
            <select id="create_padre" name="parent_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
              <option value="">Cargando...</option>
            </select>
          </div>
          <div class="mb-5">
            <label for="codigo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Código:</label>
            <input type="text" id="codigo" name="codigo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
          </div>
          <div class="mb-5">
            <label for="linea" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Linea:</label>
            <input type="text" id="nombre" name="nombre" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
          </div>
          <div class="mb-5">
            <label for="resolucion" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Resolución:</label>
            <input type="text" id="resolucion" name="resolucion" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
          </div>
          <button type="submit" class="text-white w-full bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Guardar</button>
        </form>
      </div>
    </div>
  </div>
  <script type="module">
    $(document).ready(function() {
      let ajax_url = 'http://localhost:8000/ajaxGetLineasInvestigacionFacultad/' + $('#facultad').val();
      let table = new DataTable('#lineas_table', {
        paging: true,
        pagingType: 'full_numbers',
        deferRender: true,
        processing: true,
        lengthChange: false,
        scrollX: true,
        ajax: ajax_url,
        columns: [{
            className: 'dt-control',
            orderable: false,
            data: null,
            defaultContent: ''
          },
          {
            data: 'codigo'
          },
          {
            data: 'nombre'
          },
          {
            data: 'resolucion'
          },
        ],
        //  Idioma dela información mostrada
        language: {
          zeroRecords: "No se encontraron resultados",
          info: "Mostrando _START_ - _END_ de _TOTAL_ registros.",
          infoEmpty: "No hay registros ...",
          infoFiltered: "(filtrado de _MAX_ registros)",
          sSearch: "Buscar:",
          sProcessing: "Cargando data...",
          oPaginate: {
            sFirst: "Primero",
            sLast: "Último",
            sNext: "Siguiente",
            sPrevious: "Anterior"
          },
        }
      });
      //  Imprimir contenido de hijos como filas
      function format(d) {
        let content = '<tr>' +
          '<td></td>' +
          '<td class="text-sm font-medium text-gray-900">Código</td>' +
          '<td class="text-sm font-medium text-gray-900">Nombre</td>' +
          '<td class="text-sm font-medium text-gray-900">Resolución</td>' +
          '</tr>';
        d.map((item) => {
          content = content + (
            '<tr>' +
            '<td></td>' +
            '<td>' +
            item.codigo +
            '</td>' +
            '<td>' +
            item.nombre +
            '</td>' +
            '<td>' +
            (item.resolucion == null ? "" : item.resolucion) +
            '</td>' +
            '</tr>'
          );
        })
        return content
      }
      //  Mostrar hijos
      table.on('click', 'td.dt-control', function(e) {
        let tr = e.target.closest('tr');
        let row = table.row(tr);

        if (row.child.isShown()) {
          row.child.hide();
        } else {
          row.child(format(row.data().hijos)).show();
        }
      });
      /* Actualizar al cambiar */
      $('#facultad').on('change', function() {
        ajax_url = 'http://localhost:8000/ajaxGetLineasInvestigacionFacultad/' + $('#facultad').val();
        table.ajax.url(ajax_url).load();
      });
    });
  </script>
</body>

</html>