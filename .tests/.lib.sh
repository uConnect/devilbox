#!/bin/sh -eu


if [ "${#}" != "1" ]; then
	exit 1
fi

if [ ! -d "${1}" ]; then
	exit 1
fi

DEVILBOX_PATH="${1}"


################################################################################
#
#  G E T   D E F A U L T S
#
################################################################################

###
### Default enabled Docker Versions
###
get_default_version_httpd() {
	_default="$( grep -E '^HTTPD_SERVER=' "${DEVILBOX_PATH}/env-example" | sed 's/^.*=//g' )"
	echo "${_default}"
}
get_default_version_mysql() {
	_default="$( grep -E '^MYSQL_SERVER=' "${DEVILBOX_PATH}/env-example" | sed 's/^.*=//g' )"
	echo "${_default}"
}
get_default_version_postgres() {
	_default="$( grep -E '^POSTGRES_SERVER=' "${DEVILBOX_PATH}/env-example" | sed 's/^.*=//g' )"
	echo "${_default}"
}
get_default_version_php() {
	_default="$( grep -E '^PHP_SERVER=' "${DEVILBOX_PATH}/env-example" | sed 's/^.*=//g' )"
	echo "${_default}"
}

###
### Default enabled Host Ports
###
get_default_port_httpd() {
	_default="$( grep -E '^HOST_PORT_HTTPD=' "${DEVILBOX_PATH}/env-example" | sed 's/^.*=//g' )"
	echo "${_default}"
}
get_default_port_mysql() {
	_default="$( grep -E '^HOST_PORT_MYSQL=' "${DEVILBOX_PATH}/env-example" | sed 's/^.*=//g' )"
	echo "${_default}"
}
get_default_port_postgres() {
	_default="$( grep -E '^HOST_PORT_POSTGRES=' "${DEVILBOX_PATH}/env-example" | sed 's/^.*=//g' )"
	echo "${_default}"
}

###
### Default enabled Host Mounts
###
get_default_mount_httpd() {
	_default="$( grep -E '^HOST_PATH_TO_WWW_DOCROOTS=' "${DEVILBOX_PATH}/env-example" | sed 's/^.*=//g' )"
	_prefix="$( echo "${_default}" | cut -c-1 )"

	# Relative path?
	if [ "${_prefix}" = "." ]; then
		_default="$( echo "${_default}" | sed 's/^\.//g' )" # Remove leading dot: .
		_default="$( echo "${_default}" | sed 's/^\///' )" # Remove leading slash: /
		echo "${DEVILBOX_PATH}/${_default}"
	else
		echo "${_default}"
	fi
}
get_default_mount_mysql() {
	_default="$( grep -E '^HOST_PATH_TO_MYSQL_DATADIR=' "${DEVILBOX_PATH}/env-example" | sed 's/^.*=//g' )"
	_prefix="$( echo "${_default}" | cut -c-1 )"

	# Relative path?
	if [ "${_prefix}" = "." ]; then
		_default="$( echo "${_default}" | sed 's/^\.//g' )" # Remove leading dot: .
		_default="$( echo "${_default}" | sed 's/^\///' )" # Remove leading slash: /
		echo "${DEVILBOX_PATH}/${_default}"
	else
		echo "${_default}"
	fi
}
get_default_mount_postgres() {
	_default="$( grep -E '^HOST_PATH_TO_POSTGRES_DATADIR=' "${DEVILBOX_PATH}/env-example" | sed 's/^.*=//g' )"
	_prefix="$( echo "${_default}" | cut -c-1 )"

	# Relative path?
	if [ "${_prefix}" = "." ]; then
		_default="$( echo "${_default}" | sed 's/^\.//g' )" # Remove leading dot: .
		_default="$( echo "${_default}" | sed 's/^\///' )" # Remove leading slash: /
		echo "${DEVILBOX_PATH}/${_default}"
	else
		echo "${_default}"
	fi
}


################################################################################
#
#  G E T   A L L  D O C K E R   V E R S I O N S
#
################################################################################

###
### All Docker Versions
###
get_all_docker_httpd() {
	_all="$( grep -E '^#?HTTPD_SERVER=' "${DEVILBOX_PATH}/env-example" | sed 's/.*=//g' )"
	echo "${_all}"
}
get_all_docker_mysql() {
	_all="$( grep -E '^#?MYSQL_SERVER=' "${DEVILBOX_PATH}/env-example" | sed 's/.*=//g' )"
	echo "${_all}"
}
get_all_docker_postgres() {
	_all="$( grep -E '^#?POSTGRES_SERVER=' "${DEVILBOX_PATH}/env-example" | sed 's/.*=//g' )"
	echo "${_all}"
}
get_all_docker_php() {
	_all="$( grep -E '^#?PHP_SERVER=' "${DEVILBOX_PATH}/env-example" | sed 's/.*=//g' )"
	echo "${_all}"
}


################################################################################
#
#  S E T   /  R E S E T   F U N C T I O N S
#
################################################################################

###
### Recreate .env file from env-example
###
reset_env_file() {

	# Re-create .env file
	if [ -f "${DEVILBOX_PATH}/.env" ]; then
		rm -f "${DEVILBOX_PATH}/.env"
	fi
	cp "${DEVILBOX_PATH}/env-example" "${DEVILBOX_PATH}/.env"
}

###
### Comment out all docker versions
###
comment_all_dockers() {
	_httpd="$( get_default_version_httpd )"
	_mysql="$( get_default_version_mysql )"
	_postgres="$( get_default_version_postgres )"
	_php="$( get_default_version_php )"


	# Comment out all enabled docker versions
	sed -i'' "s/HTTPD_SERVER=${_httpd}/#HTTPD_SERVER=${_httpd}/g" "${DEVILBOX_PATH}/.env"
	sed -i'' "s/MYSQL_SERVER=${_mysql}/#MYSQL_SERVER=${_mysql}/g" "${DEVILBOX_PATH}/.env"
	sed -i'' "s/POSTGRES_SERVER=${_postgres}/#POSTGRES_SERVER=${_postgres}/g" "${DEVILBOX_PATH}/.env"
	sed -i'' "s/PHP_SERVER=${_php}/#PHP_SERVER=${_php}/g" "${DEVILBOX_PATH}/.env"
}

###
### Eenable desired docker version
###
enable_docker_httpd() {
	_docker_version="${1}"
	sed -i'' "s/#HTTPD_SERVER=${_docker_version}/HTTPD_SERVER=${_docker_version}/g" "${DEVILBOX_PATH}/.env"
}
enable_docker_mysql() {
	_docker_version="${1}"
	sed -i'' "s/#MYSQL_SERVER=${_docker_version}/MYSQL_SERVER=${_docker_version}/g" "${DEVILBOX_PATH}/.env"
}
enable_docker_postgres() {
	_docker_version="${1}"
	sed -i'' "s/#POSTGRES_SERVER=${_docker_version}/POSTGRES_SERVER=${_docker_version}/g" "${DEVILBOX_PATH}/.env"
}
enable_docker_php() {
	_docker_version="${1}"
	sed -i'' "s/#PHP_SERVER=${_docker_version}/PHP_SERVER=${_docker_version}/g" "${DEVILBOX_PATH}/.env"
}


set_host_port_httpd() {
	_port="${1}"
	sed -i'' "s/^HOST_PORT_HTTPD=.*/HOST_PORT_HTTPD=${_port}/" "${DEVILBOX_PATH}/.env"
}
set_host_port_mysql() {
	_port="${1}"
	sed -i'' "s/^HOST_PORT_MYSQL=.*/HOST_PORT_MYSQL=${_port}/" "${DEVILBOX_PATH}/.env"
}
set_host_port_pgsql() {
	_port="${1}"
	sed -i'' "s/^HOST_PORT_POSTGRES=.*/HOST_PORT_POSTGRES=${_port}/" "${DEVILBOX_PATH}/.env"
}



################################################################################
#
#   S T A R T / S T O P   T H E   D E V I L B O X
#
################################################################################

devilbox_start() {
	_httpd="$1"
	_mysql="$2"
	_pysql="$3"
	_php="$4"
	_head="$5"

	echo "################################################################################"
	echo "#"
	echo "# ${_head}: [HTTPD: ${_httpd} | MYSQL: ${_mysql} | PGSQL: ${_pysql} | PHP: ${_php}] "
	echo "#"
	echo "################################################################################"

	# Adjust .env
	comment_all_dockers
	enable_docker_httpd "${_httpd}"
	enable_docker_mysql "${_mysql}"
	enable_docker_postgres "${_pysql}"
	enable_docker_php "${_php}"

	# Stop existing dockers
	cd "${DEVILBOX_PATH}" || exit 1
	docker-compose down || true
	docker-compose stop || true
	docker-compose kill || true
	docker-compose rm -f || true

	# Delete existing data dirs
	rm -rf "$( get_default_mount_httpd )"
	rm -rf "$( get_default_mount_mysql )"
	rm -rf "$( get_default_mount_postgres )"

	# Run
	docker-compose up -d

	# Wait for it to come up
	sleep 20
}

debilbox_test() {
	count="$( curl -q localhost 2>/dev/null | grep -c OK )"
	echo "${count}"
	if [ "${count}" = "0" ]; then
		curl localhost
		return false
	fi
}

devilbox_stop() {
	# Stop existing dockers
	cd "${DEVILBOX_PATH}" || exit 1
	docker-compose down || true
	docker-compose stop || true
	docker-compose kill || true
	docker-compose rm -f || true

	# Delete existing data dirs
	rm -rf "$( get_default_mount_httpd )"
	rm -rf "$( get_default_mount_mysql )"
	rm -rf "$( get_default_mount_postgres )"
}