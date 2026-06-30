<?php

require 'enviar_codigo.php';

if (
    enviarCodigo(
        'joaopedrodesousa215@gmail.com',
        '123456'
    )
) {

    echo "EMAIL ENVIADO";

} else {

    echo "ERRO AO ENVIAR";

}