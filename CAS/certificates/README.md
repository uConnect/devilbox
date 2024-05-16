# Step 1: Create the OpenSSL configuration file (in case it does not exist)
```
cat > openssl.cnf <<EOL
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
```

# Step 2: Generate a Private Key and CSR
`openssl genpkey -algorithm RSA -out cas_cert.key -pkeyopt rsa_keygen_bits:2048`

`openssl req -new -key cas_cert.key -out cas_cert.csr -config openssl.cnf`

# Step 3: Sign the CSR with the Custom CA
`openssl x509 -req -in cas_cert.csr -CA ../../ca/devilbox-ca.crt -CAkey ../../ca/devilbox-ca.key -CAcreateserial -out cas_cert.pem -days 3650 -sha256 -extfile openssl.cnf -extensions req_ext`

# Step 4: Convert PEM and key to PKCS12
`openssl pkcs12 -export -in cas_cert.pem -inkey cas_cert.key -out cas_cert.p12 -certfile ../../ca/devilbox-ca.crt`

Export Password: `changeit`

# Step 5: Import PKCS12 into JKS
`keytool -importkeystore -srckeystore cas_cert.p12 -srcstoretype PKCS12 -destkeystore keystore -deststoretype JKS`

Destination keystore password: `changeit`

