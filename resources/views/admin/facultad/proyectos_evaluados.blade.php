<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf_token" content="{{ csrf_token() }}" />
  <title>Proyectos evaluados</title>
  @vite(['resources/scss/app.scss', 'resources/js/app.js'])

</head>

<body>
  @include('admin.components.navbar')
  <div class="text-bg-secondary">
    <p class="container-fluid"><strong>Proyectos evaluados</strong></p>
  </div>

  <div class="container mb-4">
    <!--  Tab list  -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="listar-tab" data-bs-toggle="tab" data-bs-target="#listar-tab-pane"
          type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Listado</button>
      </li>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom" id="myTabContent">
      <div class="tab-pane fade show active p-4" id="listar-tab-pane" role="tabpanel" aria-labelledby="listar-tab"
        tabindex="0">
        <!--Filtro 1-->
        <form class="row mb-4">
          <div class="col-sm-3">
            Año
            <select id="opciones1" class="form-select" style="margin-top: 7px;">
              <option value="2024" selected>2024</option>
              <option value="2023">2023</option>
              <option value="2022">2022</option>
              <option value="2021">2021</option>
              <option value="2020">2020</option>
              <option value="2019">2019</option>
              <option value="2018">2018</option>
              <option value="2017">2017</option>
            </select>
          </div>
          <div class="col-sm-3">
            Tipo proyecto
            <select id="opciones2" class="form-select" style="margin-top: 7px;">
              <option value="PCONFIGI" selected>PCONFIGI</option>
              <option value="PCONFIGI-INV">PCONFIGI-INV</option>
              <option value="ECI">ECI</option>
              <option value="PEVENTO">PEVENTO</option>
              <option value="PINTERDIS">PINTERDIS</option>
              <option value="PINVPOS">PINVPOS</option>
              <option value="PMULTI">PMULTI</option>
              <option value="PRO-CTIE">PRO-CTIE</option>
              <option value="PSINFINV">PSINFINV</option>
              <option value="PSINFINU">PSINFINU</option>
              <option value="PTPBACHILLER">PTPBACHILLER</option>
              <option value="PTPDOCTO">PTPDOCTO</option>
              <option value="PTPGRADO">PTPGRADO</option>
              <option value="PTPMAEST">PTPMAEST</option>
              <option value="RFPLU">RFPLU</option>
              <option value="SPINOFF">SPINOFF</option>
            </select>
          </div>
        </form>


        <!--  Tabla  -->
        <div class="overflow-x-hidden">
          <table id="table" class="table table-striped table-hover align-middle" style="width:100%">
            <thead>
              <tr>
                <th>
                  epa_id
                </th>
                <th>
                  ID
                </th>
                <th>
                  Evaluador
                </th>
                <th>
                  Tipo
                </th>
                <th>
                  Título
                </th>
                <th>
                  Facultad
                </th>
                <th>
                  Linea
                </th>
                <th>
                  Periodo
                </th>
                <th>
                  Total opciones
                </th>
                <th>
                  Opciones evaluadas
                </th>
                <th>
                  Evaluado
                </th>
                <th>
                  Ficha
                </th>
                <th>
                  ***
                </th>
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                <th>
                  epa_id
                </th>
                <th>
                  ID
                </th>
                <th>
                  Evaluador
                </th>
                <th>
                  Tipo
                </th>
                <th>
                  Título
                </th>
                <th>
                  Facultad
                </th>
                <th>
                  Linea
                </th>
                <th>
                  Periodo
                </th>
                <th>
                  Total opciones
                </th>
                <th>
                  Opciones evaluadas
                </th>
                <th>
                  Evaluado
                </th>
                <th>
                  Ficha
                </th>
                <th>
                  ***
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!--  Toast para notificaciones -->
  @extends('admin.components.toast')

  <script type="module">
    $(document).ready(function() {
      //  Datatable
      let ajax_url = 'http://localhost:8000/api/admin/facultad/getAllProyectosEvaluados/' + $('#opciones1').val() +
        '/' + $('#opciones2').val()
      let table = new DataTable('#table', {
        paging: true,
        pagingType: 'full_numbers',
        deferRender: true,
        processing: true,
        lengthChange: false,
        scrollX: true,
        ajax: ajax_url,
        columns: [{
            data: 'id'
          },
          {
            data: 'proyecto_id'
          },
          {
            data: 'evaluador'
          },
          {
            data: 'tipo_proyecto'
          },
          {
            data: 'titulo'
          },
          {
            data: 'facultad'
          },
          {
            data: 'linea'
          },
          {
            data: 'periodo'
          },
          {
            data: 'opciones_evaluadas'
          },
          {
            data: 'opciones_evaluadas'
          },
          {
            render: function(data, type, row) {

              return row.opciones_evaluadas == 15 ?
                `<div class="alert alert-success">
              <strong>Sí</strong></div>` :
                `<div class="alert alert-danger">
              <strong>No</strong></div>`;
            }
          },
          {
            render: function(data, type, row) {
              return row.opciones_evaluadas == 15 ?
                `<div class="alert alert-success">
                <strong>Sí</strong></div>` :
                `<div class="alert alert-danger">
                <strong>No</strong></div>`;
            }
          },
          {
            data: 'opciones_evaluadas'
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

      //  Actualizar al cambiar de valor
      $('#opciones1').on('change', function() {
        ajax_url = 'http://localhost:8000/api/admin/facultad/getAllProyectosEvaluados/' + $('#opciones1').val() +
          '/' + $('#opciones2').val();
        table.clear().draw();
        table.ajax.url(ajax_url).load();
      });

      $('#opciones2').on('change', function() {
        ajax_url = 'http://localhost:8000/api/admin/facultad/getAllProyectosEvaluados/' + $('#opciones1').val() +
          '/' + $('#opciones2').val();
        table.clear().draw();
        table.ajax.url(ajax_url).load();
      });
    });
  </script>

</body>

</html>
