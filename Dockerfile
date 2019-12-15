FROM lsiobase/nginx:3.10

# set version label
ARG BUILD_DATE
ARG VERSION
ARG BARCODEBUDDY_RELEASE
LABEL build_version="Linuxserver.io version:- ${VERSION} Build-date:- ${BUILD_DATE}"
LABEL maintainer=""

RUN \
 echo "**** install build packages ****" && \
 apk add --no-cache --virtual=build-dependencies \
	git \
#	composer \
	yarn && \
 echo "**** install runtime packages ****" && \
 apk add --no-cache \
	curl \
	php7 \
	php7-gd \
#	php7-pdo \
#	php7-pdo_sqlite \
    php7-sqlite3 \
    php7-curl \
    php7-opcache \
    php7-apcu \
	php7-tokenizer && \
 echo "**** install grocy ****" && \
 mkdir -p /app/barcodebuddy && \
 if [ -z ${BARCODEBUDDY_RELEASE+x} ]; then \
	BARCODEBUDDY_RELEASE=$(curl -sX GET "https://api.github.com/repos/Forceu/barcodebuddy/releases/latest" \
	| awk '/tag_name/{print $4;exit}' FS='[""]'); \
 fi && \
 curl -o \
	/tmp/barcodbuddy.tar.gz -L \
	"https://github.com/Forceu/barcodebuddy/archive/${BARCODEBUDDY_RELEASE}.tar.gz" && \
 tar xf \
	/tmp/barcodebuddy.tar.gz -C \
	/app/barcodebuddy/ --strip-components=1 && \
 cp -R /app/barcodebuddy/data/plugins \
	/defaults/plugins && \
# echo "**** install composer packages ****" && \
# composer install -d /app/barcodebuddy --no-dev && \
# echo "**** install yarn packages ****" && \
# cd /app/barcodebuddy && \
# yarn && \
 echo "**** cleanup ****" && \
 apk del --purge \
	build-dependencies && \
 rm -rf \
	/root/.cache \
	/tmp/*

# copy local files
COPY root/ /

# ports and volumes
EXPOSE 6781
VOLUME /config
