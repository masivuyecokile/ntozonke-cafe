<?php

    function jsonResponse(bool $success, string $message, array $extra =[], int $statusCode = 200):void{

        http_response_code($statusCode);

        header('Content-Type: application/json');

        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));

        exit;

    }