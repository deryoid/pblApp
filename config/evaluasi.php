<?php

return [
    'defaults' => [
        // Weights: Dosen set
        'd_hasil'      => 30,
        'd_teknis'     => 20,
        'd_user'       => 15,
        'd_efisiensi'  => 10,
        'd_dokpro'     => 15,
        'd_inisiatif'  => 10,

        // Weights: Mitra set (AP detail)
        'm_kehadiran'  => 50,
        'm_presentasi' => 50,

        // Global weights
        'w_dosen'      => 80,
        'w_mitra'      => 20,
        'w_kelompok'   => 70,
        'w_ap'         => 30,
        'w_ap_kehadiran'  => 50,
        'w_ap_presentasi' => 50,
    ],
];

