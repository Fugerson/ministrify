#!/bin/bash
# Generate release keystore for Ministrify Android app
# Run this once, then never lose the keystore file!

set -e

KEYSTORE_FILE="ministrify-release.keystore"
KEY_ALIAS="ministrify"

if [ -f "$KEYSTORE_FILE" ]; then
    echo "Keystore already exists: $KEYSTORE_FILE"
    echo "To regenerate, delete it first."
    exit 1
fi

echo "Generating release keystore..."
echo ""

keytool -genkeypair \
    -v \
    -storetype PKCS12 \
    -keystore "$KEYSTORE_FILE" \
    -alias "$KEY_ALIAS" \
    -keyalg RSA \
    -keysize 2048 \
    -validity 10000 \
    -dname "CN=Ministrify, OU=Mobile, O=Ministrify, L=Kyiv, ST=Kyiv, C=UA"

echo ""
echo "Keystore created: $KEYSTORE_FILE"
echo ""
echo "Now create keystore.properties with your passwords:"
echo "  cp keystore.properties.example keystore.properties"
echo "  # Edit keystore.properties with your passwords"
echo ""
echo "SHA-256 fingerprint (for assetlinks.json):"
keytool -list -v -keystore "$KEYSTORE_FILE" -alias "$KEY_ALIAS" 2>/dev/null | grep "SHA256:"
echo ""
echo "IMPORTANT: Back up $KEYSTORE_FILE securely! If lost, you cannot update the app."
