<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf_token" content="{{ csrf_token() }}" />
  <title>Lineas de investigación</title>
  @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body>
  @include('admin.components.navbar')
  <div class="container mb-4">
    <h4 class="my-4">Líneas de investigación</h4>
    <!--  Tab list  -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="listar-tab" data-bs-toggle="tab" data-bs-target="#listar-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Listar líneas</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="crear-tab" data-bs-toggle="tab" data-bs-target="#crear-tab-pane" type="button" role="tab" aria-controls="crear-tab-pane" aria-selected="false">Crear línea</button>
      </li>
    </ul>
    <div class="tab-content border border-top-0 rounded-bottom" id="myTabContent">
      <div class="tab-pane fade show active p-4" id="listar-tab-pane" role="tabpanel" aria-labelledby="listar-tab" tabindex="0">
        <!--  Seleccionar facultad  -->
        <form class="row mb-4">
          <label for="facultad" class="col-sm-2 col-form-label">Facultad:</label>
          <div class="col-sm-10">
            <select id="facultad" class="form-select">
              <option value="null" selected>Ninguna</option>
              @foreach($facultades as $facultad)
              <option value="{{ $facultad->id }}">{{ $facultad->nombre }}</option>
              @endforeach
            </select>
          </div>
        </form>
        <!--  Tabla  -->
        <div class="overflow-x-hidden">
          <table id="table" class="table table-striped table-hover align-middle" style="width:100%">
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
            <tfoot>
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
            </tfoot>
          </table>
        </div>
      </div>
      <div class="tab-pane fade" id="crear-tab-pane" role="tabpanel" aria-labelledby="crear-tab" tabindex="0">
        <!--  Crear nuev@  -->
        <form action="{{ route('create_linea') }}" method="post" class="p-4">
          @csrf
          <div class="row mb-4">
            <label for="facultad_id" class="col-sm-2 col-form-label">Facultad:</label>
            <div class="col-sm-10">
              <select id="facultad_id" name="facultad_id" class="form-select">
                <option value="" selected>Ninguna</option>
                @foreach($facultades as $facultad)
                <option value="{{ $facultad->id }}">{{ $facultad->nombre }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mb-4">
            <label for="parent_id" class="col-sm-2 col-form-label">Padre:</label>
            <div class="col-sm-10">
              <select id="parent_id" name="parent_id" class="form-select">
                <option value="">Cargando...</option>
              </select>
            </div>
          </div>
          <div class="row mb-4">
            <label for="codigo" class="col-sm-2 col-form-label">Código:</label>
            <div class="col-sm-10">
              <input type="text" id="codigo" name="codigo" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="nombre" class="col-sm-2 col-form-label">Linea:</label>
            <div class="col-sm-10">
              <input type="text" id="nombre" name="nombre" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="resolucion" class="col-sm-2 col-form-label">Resolución:</label>
            <div class="col-sm-10">
              <input type="text" id="resolucion" name="resolucion" class="form-control">
            </div>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script type="module">
    $(document).ready(function() {
      let ajax_url = 'http://localhost:8000/api/lineasInvestigacion/getAllFacultad/' + $('#facultad').val();
      let table = new DataTable('#table', {
        paging: true,
        pagingType: 'full_numbers',
        deferRender: true,
        processing: true,
        lengthChange: false,
        scrollX: true,
        ajax: {
          url: ajax_url,
          cache: true
        },
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
      //  Imprimir contenido de hijos como filas
      function format(d) {
        let childs = [];
        d.map((item) => {
          let content = '<td></td>' +
            '<td class="ps-3">' +
            item.codigo +
            '</td>' +
            '<td class="ps-3">' +
            item.nombre +
            '</td>' +
            '<td class="ps-3">' +
            (item.resolucion == null ? "" : item.resolucion) +
            '</td>';
          childs.push($('<tr>').addClass('bg-primary').append(content)[0])
        })
        return childs;
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
        ajax_url = 'http://localhost:8000/api/lineasInvestigacion/getAllFacultad/' + $('#facultad').val();
        table.ajax.url(ajax_url).load();
      });
    });
  </script>
</body>

</html>