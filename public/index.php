<?php

/**
 * public/index.php — Front Controller
 *
 * Responsabilidade: carregar as classes necessárias e delegar
 * o roteamento para Rotas/rotas.php. Não contém regras de negócio.
 */

require __DIR__ . '/../Configuracao/configuracao.php';
require __DIR__ . '/../Modelo/Conexao.php';
require __DIR__ . '/../Modelo/OrdemManutencao.php';
require __DIR__ . '/../Controlador/ControladorOrdemManutencao.php';

require __DIR__ . '/../Rotas/rotas.php';
