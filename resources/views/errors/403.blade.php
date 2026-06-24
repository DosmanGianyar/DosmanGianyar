@extends('errors.layout', [
    'code'    => '403',
    'title'   => 'Akses Ditolak',
    'message' => $exception->getMessage() ?: 'Kamu tidak memiliki izin untuk mengakses halaman ini.',
])
