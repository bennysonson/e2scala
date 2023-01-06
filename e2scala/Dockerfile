FROM wordpress

RUN curl -fsSOL https://phar.phpunit.de/phpunit.phar &&\
    chmod +x phpunit.phar &&\
    mv phpunit.phar /urs/local/bin/phpunit &&\
    phpunit --version

ARG plugin_path

RUN mkdir -p ${plugin_path}/tests/phpunit/tests

RUN apt update &&\
    apt install -y --no-install-recommends subversion &&\
    svn co https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/ ${plugin_path}/tests/phpunit &&\
    apt remove --purge subversion &&\
    rm -rf /var/lib/apt/lists/*

