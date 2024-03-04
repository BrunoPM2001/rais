<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf_token" content="{{ csrf_token() }}" />
  <title>Asignación de evaluadores</title>
  @vite(['resources/scss/app.scss', 'resources/js/app.js'])
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
  @include('admin.components.navbar')
  <div class="text-bg-secondary">
    <p class="container-fluid"><strong>Evaluadores proyectos</strong></p>
  </div>

  <div class="container mb-4">
    <!--  Tab list  -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="listar-tab" data-bs-toggle="tab" data-bs-target="#listar-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Listado</button>
      </li>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom" id="myTabContent">
      <div class="tab-pane fade show active p-4" id="listar-tab-pane" role="tabpanel" aria-labelledby="listar-tab" tabindex="0">
        <!--Filtro 1-->
        <form class="row mb-4">
          <div class="col-sm-2">
            <select id="opciones" class="form-select">
              <option value="opcion1" selected>Sin evaluador</option>
              <option value="opcion2">Con evaluador</option>
              <option value="opcion3">Todos</option>
            </select>
          </div>
          <div class="col-sm-6">
            <div class="row">
              <div class="col">
                <div class="d-flex justify-content-start">
                  <button id="botonA" type="button" class="btn btn-primary">Asignar evaluador</button>
                  <button id="botonB" type="button" class="btn btn-info ml-2" style="display: none;">Editar evaluador</button>
                </div>
              </div>
            </div>
          </div>
        </form>


        <!--  Tabla  -->
        <div class="overflow-x-hidden">
          <table id="table" class="table table-striped table-hover align-middle" style="width:100%">
            <thead>
              <tr>
                <th>
                  ID
                </th>
                <th>
                  Tipo
                </th>
                <th>
                  Linea
                </th>
                <th>
                  Facultad del grupo
                </th>
                <th>
                  Título
                </th>
                <th>
                  Evaluadores
                </th>
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                <th>
                  ID
                </th>
                <th>
                  Tipo
                </th>
                <th>
                  Linea
                </th>
                <th>
                  Facultad del grupo
                </th>
                <th>
                  Título
                </th>
                <th>
                  Evaluadores
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para Asignar -->
  @extends('admin.components.modal')
  @section('form')
  <form id="updateForm">
    @endsection

    @section('titulo')
    Evaluadores
    @endsection

    @section('contenido')
    <input type="text" hidden id="edit_id" name="edit_id">
    <input type="text" hidden id="tabla_id" name="tabla_id">
    <div class="row align-items-center mb-3">
      <label for="asignar_evaluador1" class="col-sm-4 col-form-label">Evaluador 1</label>
      <div class="col-sm-8">
          <div class="row">
            <div class="col">
              <div class="d-flex justify-content-start">
                <input type="text" id="asignar_evaluador1" name="asignar_evaluador1" class="form-control">
                <input type="text" hidden id="id1" name="id1">
                <div class="dropdown-menu" id="autocomplete-dropdown1" style="display:none">
                  <!-- Aquí se mostrarán los resultados -->
                </div>
                <button class="btn btn-dark ml-1">icono</button>
              </div>
            </div>
          </div>
      </div>
    </div>

    <div class="row align-items-center mb-3">
      <label for="asignar_evaluador2" class="col-sm-4 col-form-label">Evaluador 2</label>
      <div class="col-sm-8">
          <div class="row">
            <div class="col">
              <div class="d-flex justify-content-start">
                <input type="text" id="asignar_evaluador2" name="asignar_evaluador2" class="form-control">
                <input type="text" hidden id="id2" name="id2">
                <div class="dropdown-menu" id="autocomplete-dropdown2" style="display:none">
                  <!-- Aquí se mostrarán los resultados -->
                </div>
                <button class="btn btn-dark ml-1">icono</button>
              </div>
            </div>
          </div>
      </div>
    </div>

    <div class="row align-items-center mb-3">
      <label for="asignar_evaluador3" class="col-sm-4 col-form-label">Evaluador 3</label>
      <div class="col-sm-8">
          <div class="row">
            <div class="col">
              <div class="d-flex justify-content-start">
                <input type="text" id="asignar_evaluador3" name="asignar_evaluador3" class="form-control">
                <input type="text" hidden id="id3" name="id3">
                <div class="dropdown-menu" id="autocomplete-dropdown3" style="display:none">
                  <!-- Aquí se mostrarán los resultados -->
                </div>
                <button class="btn btn-dark ml-1">icono</button>
              </div>
            </div>
          </div>
      </div>
    </div>
    
    @endsection

    @section('end_form')
  </form>
  @endsection

  <!--  Toast para notificaciones -->
  @extends('admin.components.toast')

  <script type="module">
    $(document).ready(function() {
    //  Iniciar modal y toast
    let modal = new bootstrap.Modal(document.getElementById('myModal'), {});
    let toast = new bootstrap.Toast(document.getElementById('myToast'));
    let temp;
    let tipoModal;

    function mostrarBotones() {
        var opciones = $("#opciones");
        var botonA = $("#botonA");
        var botonB = $("#botonB");

        // Ocultar todos los botones al principio
        botonA.show()
        botonB.hide()

        // Mostrar los botones según la opción seleccionada
        if (opciones.val() === "opcion1") {
          botonA.show()
          botonB.hide()
        } else if (opciones.val() === "opcion2") {
            botonA.hide()
            botonB.show()
        } else if (opciones.val() === "opcion3") {
            botonA.show()
            botonB.show()
        }
    }
    // Llamar a la función al cargar la página
    //window.onload = mostrarBotones;
    $("#opciones").on("change",mostrarBotones)

    // Función de validación al hacer clic en "Editar evaluador"
    function editarEvaluador() {
        var table = $('#table').DataTable();
        var selectedRows = table.rows({ selected: true }).count();

        if (selectedRows === 1) {
          // Aquí colocas la lógica para editar evaluador
          console.log("Editar evaluador para una fila seleccionada.");
          tipoModal = "Editar";
          modal.show()
          $.ajax({
            url: 'http://localhost:8000/api/admin/facultad/getEvaluadoresProyecto/' + '10295',
            type: 'GET',
            success: (data) => {
              //  Actualizar la data a editar
              data.data.forEach((item,index)=>{
                $("#asignar_evaluador" + Number(index+1)).val(item.evaluador)
                console.log(Number(index+1))
                console.log(item)
              })
            }
          });
        } else {
          alert("Por favor, seleccione solo un proyecto con evaluadores asigandos.");
        }
    }

    // Función de validación al hacer clic en "Asignar evaluador"
    function asignarEvaluador() {
        var table = $('#table').DataTable();
        var selectedRows = table.rows({ selected: true }).count();

        if (selectedRows === 0) {
          alert("Seleccione al menos un proyecto.");
        } else {
          // Aquí colocas la lógica para asignar evaluador
          console.log("Asignar evaluador para una fila seleccionada.");
          modal.show()
        }
    }

    $("#botonA").on("click",asignarEvaluador)
    $("#botonB").on("click",editarEvaluador)

      // ********Función para mostrar las sugerencias*******
      function showSuggestions(data,p_autocomplete) {
        var dropdownMenu = $(p_autocomplete);
        dropdownMenu.empty();

        data.forEach(function(suggestion) {
          var item = $('<a class="dropdown-item" href="#" myId="' + suggestion.id + '">').text(suggestion.content);
          dropdownMenu.append(item);
        });

        dropdownMenu.css('display', 'block');
      }

      // Evento keyup para el input
      function SugerenciaTexto(p_asignarEvaluador,p_autocomplete) {
        let input = $(p_asignarEvaluador).val();
        clearTimeout(temp);
        temp = setTimeout(() => {
          //  Petición ajax y uso de typeahead
          $.ajax({
            url: 'http://localhost:8000/api/admin/facultad/searchEvaluadorBy/' + input,
            method: 'GET',
            success: (data) => {
              let sugerencias = [];
              $.each(data, (index, user) => {
                sugerencias.push({
                  id: user.id,
                  content: user.apellidos + " "  + user.nombres
                });
              });
              showSuggestions(sugerencias,p_autocomplete)
            }
          });
        }, 1000);
        if (input.length == 0) {
          $(p_autocomplete).css('display', 'none');
        }
      }

      $('#asignar_evaluador1').on('keyup',()=>SugerenciaTexto("#asignar_evaluador1","#autocomplete-dropdown1"));
      $('#asignar_evaluador2').on('keyup',()=>SugerenciaTexto("#asignar_evaluador2","#autocomplete-dropdown2"));
      $('#asignar_evaluador3').on('keyup',()=>SugerenciaTexto("#asignar_evaluador3","#autocomplete-dropdown3"));

      function AutocompleteTexto(p_asignarEvaluador,p_id,p_autocomplete,contexto) {
        var id = $(contexto).attr("myId");
        var text = $(contexto).text();
        console.log(text)
        $(p_asignarEvaluador).val(text);
        $(p_id).val(id);
        $(p_autocomplete).css('display', 'none');
      }

      // Evento click para las sugerencias
      $('#autocomplete-dropdown1').on('click', '.dropdown-item', function(e){
        AutocompleteTexto("#asignar_evaluador1","#id1","#autocomplete-dropdown1",this)
      });
      $('#autocomplete-dropdown2').on('click', '.dropdown-item', function(e){
        AutocompleteTexto("#asignar_evaluador2","#id2","#autocomplete-dropdown2",this)
      });
      $('#autocomplete-dropdown3').on('click', '.dropdown-item', function(e){
        AutocompleteTexto("#asignar_evaluador3","#id3","#autocomplete-dropdown3",this)
      });

      // Evento blur para ocultar las sugerencias cuando se hace clic fuera del input
      $(document).on('click', function(event) {
        if (!$(event.target).closest('.input-group').length) {
          $('#autocomplete-dropdown1').css('display', 'none');
          $('#autocomplete-dropdown2').css('display', 'none');
          $('#autocomplete-dropdown3').css('display', 'none');
        }
      });
      //***********************************

    });
  </script>


  <script type="module">
    $(document).ready(function() {
      //  Iniciar tabla, toast y modal
      let toast = new bootstrap.Toast(document.getElementById('myToast'));
      //  Datatable
      let ajax_url = 'http://localhost:8000/api/admin/facultad/getAllEvaluadores'
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
            data: 'tipo_proyecto'
          },
          {
            data: 'linea'
          },
          {
            data: 'facultad'
          },
          {
            data: 'titulo'
          },
          {
            data: 'evaluadores'
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
        },
        select: {
        style: 'multi'
        }
      });
    });
  </script>

</body>

</html>