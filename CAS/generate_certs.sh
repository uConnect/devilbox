#!/bin/sh

CERT_DIR="/certs"
CA_CERT="/ca/devilbox-ca.crt"
CA_KEY="/ca/devilbox-ca.key"
CERT_KEY="$CERT_DIR/cas_cert.key"
CERT_CSR="$CERT_DIR/cas_cert.csr"
CERT_PEM="$CERT_DIR/cas_cert.pem"
CERT_P12="$CERT_DIR/cas_cert.p12"
KEYSTORE="$CERT_DIR/thekeystore"
OPENSSL_CONF="$CERT_DIR/openssl.cnf"

# Check if the certificates already exist
if [ -f "$KEYSTORE" ]; then
    echo "Certificates already exist."
else 

    # Create the /certs directory if it does not exist
    mkdir -p $CERT_DIR

    # Step 1: Create the OpenSSL configuration file
    cat > $OPENSSL_CONF <<EOL
[req]
distinguished_name = req_distinguished_name
req_extensions = req_ext
prompt = no

[req_distinguished_name]
C = US
ST = MA
L = Boston
O = uConnect
OU = IT Department
CN = localhost

[req_ext]
subjectAltName = @alt_names

[alt_names]
DNS.1 = localhost
DNS.2 = cas-server
EOL
    echo "Create the OpenSSL configuration file, Done"

    # Step 2: Generate a Private Key and CSR
    openssl genpkey -algorithm RSA -out $CERT_KEY -pkeyopt rsa_keygen_bits:2048
    openssl req -new -key $CERT_KEY -out $CERT_CSR -config $OPENSSL_CONF
    echo "Generate a Private Key and CSR, Done"

    # Step 3: Sign the CSR with the Custom CA
    openssl x509 -req -in $CERT_CSR -CA $CA_CERT -CAkey $CA_KEY -CAcreateserial -out $CERT_PEM -days 3650 -sha256 -extfile $OPENSSL_CONF -extensions req_ext
    echo "Sign the CSR with the Custom CA, Done"

    # Step 4: Convert PEM and key to PKCS12
    openssl pkcs12 -export -in $CERT_PEM -inkey $CERT_KEY -out $CERT_P12 -certfile $CA_CERT -password pass:changeit
    echo "Convert PEM and key to PKCS12, Done"

    # Step 5: Import PKCS12 into JKS
    keytool -importkeystore -srckeystore $CERT_P12 -srcstoretype PKCS12 -destkeystore $KEYSTORE -deststoretype JKS -srcstorepass changeit -deststorepass changeit
    echo "Import PKCS12 into JKS, Done"
fi