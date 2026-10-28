<?php

namespace App\Enums;

enum Rol: string
{
    /** Gestiona empleados y ve todo. */
    case Admin = 'admin';

    /** Registra asistencias y consulta reportes. */
    case Supervisor = 'supervisor';
}
