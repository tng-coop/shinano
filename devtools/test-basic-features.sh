#!/bin/bash

#set scriptdir

echo "Resetting database..."
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
bash "$SCRIPT_DIR/../DB-model/reset-dev.sh" mysql -uroot

