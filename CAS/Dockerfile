FROM alpine/openssl:latest


# Install OpenJDK
RUN apk add --no-cache openjdk11-jre

COPY generate_certs.sh /generate_certs.sh
RUN chmod 755 /generate_certs.sh

ENTRYPOINT ["/generate_certs.sh"]