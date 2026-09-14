<?php

/**
 * public/index.php — Front Controller
 *
 * Responsabilidade: carregar as classes necessárias e delegar
 * o roteamento para Routes/api.php. Não contém regras de negócio.
 */

require __DIR__ . '/../Config/configuration.php';
require __DIR__ . '/../Model/Connection.php';
require __DIR__ . '/../Model/MaintenanceOrder.php';
require __DIR__ . '/../Controller/MaintenanceOrderController.php';

require __DIR__ . '/../Routes/api.php';
