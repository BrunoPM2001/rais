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
  <button hidden data-modal-target="edit_dependencia" data-modal-toggle="edit_dependencia"></button>
  <div class="mx-auto max-w-screen-xl p-4">
    <!--  Tabs  -->
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
      <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="default-tab" data-tabs-toggle="#default-tab-content" role="tablist">
        <li class="me-2" role="presentation">
          <button class="inline-block p-4 border-b-2 rounded-t-lg" id="listado-tab" data-tabs-target="#tab_listado" type="button" role="tab" aria-controls="tab_listado" aria-selected="false">Lista de Dependencias</button>
        </li>
        <li class="me-2" role="presentation">
          <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="crear-tab" data-tabs-target="#tab_crear" type="button" role="tab" aria-controls="tab_crear" aria-selected="false">Crear Dependencia nueva</button>
        </li>
      </ul>
    </div>
    <div id="default-tab-content">
      <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="tab_listado" role="tabpanel" aria-labelledby="profile-tab">
        <!--  Tabla de dependencias  -->
        <div class="relative p-4 overflow-x-auto shadow-md sm:rounded-lg">
          <table id="table" style="width:100%">
            <thead>
              <tr>
                <th>
                  Facultad
                </th>
                <th>
                  Dependencia
                </th>
                <th>
                  Acción
                </th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>

        </div>
      </div>
      <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="tab_crear" role="tabpanel" aria-labelledby="dashboard-tab">
        <!--  Crear nueva dependencias  -->
        <form action="{{ route('create_dependencia') }}" method="post" class="max-w-md mx-auto">
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
            <label for="nombre" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nombre de dependencia:</label>
            <input type="text" id="nombre" name="dependencia" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
          </div>
          <button type="submit" class="text-white w-full bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Guardar</button>
        </form>
      </div>
    </div>
  </div>

  @extends('admin.components.modal')

  @section('titulo_modal')
  Hola
  @endsection

  <script type="module">
    $(document).ready(function() {
      // const modal = new Modal($('#edit_dependencia'))
      let ajax_url = 'http://localhost:8000/ajaxGetDependencias'
      let table = new DataTable('#table', {
        paging: true,
        pagingType: 'full_numbers',
        deferRender: true,
        processing: true,
        lengthChange: false,
        scrollX: true,
        ajax: ajax_url,
        columns: [{
            render: function(data, type, row) {
              return row.facultad.nombre;
            }
          },
          {
            data: 'dependencia'
          },
          {
            render: function(data, type, row) {
              return `<button id="s_${row.id}" data-modal-target="edit_dependencia" data-modal-toggle="edit_dependencia" class="edit focus:outline-none text-white bg-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 p-2 me-2 mb-2 dark:focus:ring-yellow-900">Editar</button>`;
            }
          }
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
      //  Para el modal de update
      $('#table').on('click', '.edit', (e) => {
        let item = e.currentTarget.getAttribute('id');
        let [_, id] = item.split('_');
        console.log(id)
        // const getData = $.ajax({
        //   url: 'ajax/cursos/ajax.getCurso.php',
        //   type: 'GET',
        //   data: {
        //     codCurso
        //   },
        //   success: (data) => {
        //     //  Actualizar la data a editar
        //     let dat = JSON.parse(data);
        //     $("#codigo_edit").val(dat.codigo);
        //     $("#idioma_edit").val(dat.idioma);
        //     $("#programa_edit").val(dat.programa);
        //     $("#nivel_edit").val(dat.nivel);
        //     $("#horario_edit").val(dat.horario);
        //     $("#mesaño_edit").val(dat.mes_año);
        //     $("#modalidad_edit").val(dat.modalidad);
        //     $("#estado_edit").val(dat.estado);
        //     $("#docente_edit").val(dat.dniDocente);
        //     //  Bloquear estados dependiendo del estado actual
        //     blockEstados(dat.estado);
        //   }
        // });
      });
    });
  </script>
</body>

</html>