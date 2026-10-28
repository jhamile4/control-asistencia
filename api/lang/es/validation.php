<?php

/*
 * Mensajes de validación en español para las reglas que usa la API.
 * Las reglas que no estén aquí usan el mensaje en inglés (APP_FALLBACK_LOCALE).
 */
return [
    'after_or_equal' => 'El campo :attribute debe ser una fecha igual o posterior a :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha igual o anterior a :date.',
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'date' => 'El campo :attribute no es una fecha válida.',
    'date_format' => 'El campo :attribute debe tener el formato :format.',
    'digits' => 'El campo :attribute debe tener :digits dígitos.',
    'email' => 'El campo :attribute debe ser un correo válido.',
    'exists' => 'El :attribute seleccionado no existe.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'required' => 'El campo :attribute es obligatorio.',
    'required_with' => 'El campo :attribute es obligatorio cuando se envía :values.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'Ya existe un registro con ese :attribute.',

    'attributes' => [
        'apellidos' => 'apellidos',
        'area_id' => 'área',
        'activo' => 'activo',
        'buscar' => 'búsqueda',
        'desde' => 'desde',
        'dni' => 'DNI',
        'email' => 'correo',
        'empleado_id' => 'empleado',
        'fecha' => 'fecha',
        'hasta' => 'hasta',
        'hora' => 'hora',
        'hora_entrada' => 'hora de entrada',
        'nombres' => 'nombres',
        'password' => 'contraseña',
        'por_pagina' => 'resultados por página',
    ],
];
