#!/bin/bash

## Ejecutar el comando apache2-foreground en segundo plano
#apache2-foreground

# Ejecutar el comando supervisord
supervisord -c /etc/supervisor/supervisord.conf &

sleep 5
apache2-foreground
