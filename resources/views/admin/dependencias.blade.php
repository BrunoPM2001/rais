<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lineas de investigación</title>
  @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body>
  @include('admin.components.navbar')
  <div class="container">
    <h4 class="my-4">Líneas de investigación</h4>
    <!--  Tab list  -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="listar-tab" data-bs-toggle="tab" data-bs-target="#listar-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Listar dependencias</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="crear-tab" data-bs-toggle="tab" data-bs-target="#crear-tab-pane" type="button" role="tab" aria-controls="crear-tab-pane" aria-selected="false">Crear dependencia</button>
      </li>
    </ul>
    <div class="tab-content border border-top-0 rounded-bottom" id="myTabContent">
      <div class="tab-pane fade show active p-4" id="listar-tab-pane" role="tabpanel" aria-labelledby="listar-tab" tabindex="0">
        <!--  Tabla de lineas de investigación  -->
        <div class="overflow-x-hidden">
          <table id="table" class="table table-hover align-middle" style="width:100%">
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
            <tfoot>
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
            </tfoot>
          </table>
        </div>
      </div>
      <div class="tab-pane fade" id="crear-tab-pane" role="tabpanel" aria-labelledby="crear-tab" tabindex="0">
        <!--  Crear nueva linea de investigación  -->
        <form action="{{ route('create_dependencia') }}" method="post" class="p-4">
          @csrf
          <div class="row mb-4">
            <label for="create_facultad" class="col-sm-2 col-form-label">Facultad:</label>
            <div class="col-sm-10">
              <select id="create_facultad" name="facultad_id" class="form-select">
                <option value="" selected>Ninguna</option>
                @foreach($facultades as $facultad)
                <option value="{{ $facultad->id }}">{{ $facultad->nombre }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mb-4">
            <label for="dependencia" class="col-sm-2 col-form-label">Dependencia:</label>
            <div class="col-sm-10">
              <input type="text" id="dependencia" name="dependencia" class="form-control">
            </div>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal para editar -->
  @extends('admin.components.modal')
  @section('form')
  <form action="{{ route('edit_dependencia') }}" method="post">
    @endsection

    @section('titulo')
    Editar dependencia
    @endsection

    @section('contenido')
    <div class="mb-3">
      <label for="edit_facultad" class="form-label">Facultad:</label>
      <select id="edit_facultad" name="edit_facultad" class="form-select">
        <option value="" selected>Ninguna</option>
        @foreach($facultades as $facultad)
        <option value="{{ $facultad->id }}">{{ $facultad->nombre }}</option>
        @endforeach
      </select>
    </div>
    <div class="mb-3">
      <label for="edit_dependencia" class="form-label">Dependencia</label>
      <input type="text" class="form-control" id="edit_dependencia" name="edit_dependencia">
    </div>
    @endsection

    @section('end_form')
  </form>
  @endsection


  <script type="module">
    $(document).ready(function() {
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
              return `<button id="s_${row.id}" data-bs-toggle="modal" data-bs-target="#exampleModal" class="btn btn-warning edit">Editar</button>`;
            }
          }
        ],
        //  Idioma dela información mostrada
        language: {
          zeroRecords: "No se encontraron resultados",
          info: "Mostrando _START_-_END_ de _TOTAL_ registros.",
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
        const getData = $.ajax({
          url: 'http://localhost:8000/ajaxGetDependencia/' + id,
          type: 'GET',
          success: (data) => {
            //  Actualizar la data a editar
            $("#edit_facultad").val(data.facultad.nombre);
            $("#edit_dependencia").val(data.dependencia);
          }
        });
      });
    });
  </script>
</body>

</html>