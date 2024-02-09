<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario_investigador extends Model {
  protected $table = 'Usuario_investigador';

  protected $fillable = [
    'dependencia_id',
    'facultad_id',
    'instituto_id',
    'codigo',
    'codigo_orcid',
    'dina',
    'apellido1',
    'apellido2',
    'nombres',
    'doc_tipo',
    'doc_numero',
    'sexo',
    'fecha_nac',
    'grado',
    'especialidad',
    'titulo_profesional',
    'tipo',
    'docente_categoria',
    'direccion1',
    'direccion2',
    'fecha_icsi',
    'email1',
    'email2',
    'email3',
    'indice_h',
    'indice_h_url',
    'regina',
    'researcher_id',
    'scopus_id',
    'google_scholar',
    'palabras_clave',
    'telefono_casa',
    'telefono_trabajo',
    'telefono_movil',
    'teleahorro',
    'facebook',
    'twitter',
    'link',
    'pais',
    'institucion',
    'pais_institucion',
    'posicion_unmsm',
    'biografia',
    'estado',
    'tmp_facultad',
    'tmp_id',
    'enlace_cti',
    'tipo_investigador',
    'tipo_investigador_categoria',
    'tipo_investigador_programa',
    'tipo_investigador_estado',
    'renacyt',
    'renacyt_nivel',
    'cti_vitae',
    'rrhh_status',
    'dep_academico'
  ];

  protected $dates = [
    'fecha_nac',
    'fecha_icsi',
  ];
}
