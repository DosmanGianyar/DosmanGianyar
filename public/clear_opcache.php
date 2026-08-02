<?php

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPCACHE_RESET_SUCCESS";
} else {
    echo "OPCACHE_NOT_ACTIVE";
}
