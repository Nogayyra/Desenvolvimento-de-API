<?php

// Ponto de entrada da API: carrega os arquivos e passa para as rotas.

require __DIR__ . '/../Configuracao/configuracao.php';
require __DIR__ . '/../Modelo/Conexao.php';
require __DIR__ . '/../Modelo/OrdemManutencao.php';
require __DIR__ . '/../Controlador/ControladorOrdemManutencao.php';

require __DIR__ . '/../Rotas/rotas.php';
