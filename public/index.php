<?php

// Ponto de entrada da API: carrega os arquivos e passa para as rotas.

require __DIR__ . '/../Models/connection.php';
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../Models/Conexao.php';
require __DIR__ . '/../Models/OrdemManutencao.php';
require __DIR__ . '/../Controller/ControladorOrdemManutencao.php';

require __DIR__ . '/../Routes/rotas.php';
