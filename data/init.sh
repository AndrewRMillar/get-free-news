#!/bin/bash

# --- Configuratie ---
# Gebruik het eerste argument als database pad, anders een default naam
DB_PATH="${1:-data.sqlite}"
SCHEMA_FILE="${2:-schema.sql}"

echo "-----------------------------------------------"
echo "SQLite Database Initialisatie"
echo "-----------------------------------------------"

# 1. Controleer of het schema bestand bestaat
if [ ! -f "$SCHEMA_FILE" ]; then
    echo "Fout: $SCHEMA_FILE niet gevonden! Zorg dat dit bestand bestaat."
    exit 1
fi

# 2. Controleer of de database al bestaat
if [ -f "$DB_PATH" ]; then
    echo "Bericht: Database '$DB_PATH' bestaat al. Schema wordt (indien nodig) bijgewerkt..."
else
    echo "Bericht: Database '$DB_PATH' wordt aangemaakt..."
    # Maak de directory aan als die nog niet bestaat (bijv. path/to/db)
    mkdir -p "$(dirname "$DB_PATH")"
fi

# 3. Voer het SQL schema uit op de database
# We gebruiken de sqlite3 command line tool
sqlite3 "$DB_PATH" < "$SCHEMA_FILE"

# 4. Controleer of de uitvoering succesvol was
if [ $? -eq 0 ]; then
    echo "Succes: Het schema uit '$SCHEMA_FILE' is toegepast op '$DB_PATH'."
    echo "-----------------------------------------------"
    echo "Klaar voor gebruik!"
else
    echo "Fout: Er is iets misgegaan bij het uitvoeren van het SQL script."
    exit 1
fi
