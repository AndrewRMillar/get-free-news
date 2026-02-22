#!/bin/bash

# --- Configuration ---
# Use first argunent as path to database, else use default
DB_PATH="${1:-data.sqlite}"
SCHEMA_FILE="${2:-schema.sql}"

echo "-----------------------------------------------"
echo "SQLite Database Initialisatie"
echo "-----------------------------------------------"

# 1. Check if schema files exists 
if [ ! -f "$SCHEMA_FILE" ]; then
    echo "Fout: $SCHEMA_FILE niet gevonden! Zorg dat dit bestand bestaat."
    exit 1
fi

# 2. Check if database file exists
if [ -f "$DB_PATH" ]; then
    echo "Bericht: Database '$DB_PATH' bestaat al. Schema wordt (indien nodig) bijgewerkt..."
else
    echo "Bericht: Database '$DB_PATH' wordt aangemaakt..."
    # Create directory if it doesn't exist
    mkdir -p "$(dirname "$DB_PATH")"
fi

# 3. Execute migration to database
sqlite3 "$DB_PATH" < "$SCHEMA_FILE"

# 4. Check if execution was successful
if [ $? -eq 0 ]; then
    echo "Succes: Het schema uit '$SCHEMA_FILE' is toegepast op '$DB_PATH'."
    echo "-----------------------------------------------"
    echo "Klaar voor gebruik!"
else
    echo "Fout: Er is iets misgegaan bij het uitvoeren van het SQL script."
    exit 1
fi
