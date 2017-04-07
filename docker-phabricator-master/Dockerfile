#
# Docker image for running https://github.com/phacility/phabricator
#

FROM    debian:jessie
MAINTAINER  Swapnil Patil <Swapnil.patil@exadatum.com>

ENV DEBIAN_FRONTEND=noninteractive DEBCONF_NONINTERACTIVE_SEEN=true

# TODO: review this dependency list
RUN     apt-get clean && apt-get update && apt-get install -y \
	        git \
            apache2 \
            curl \
            libapache2-mod-php5 \
            libmysqlclient18 \
            mercurial \
            mysql-client \
            php-apc \
            php5 \
            php5-apcu \
            php5-cli \
            php5-curl \
            php5-gd \
            php5-json \
            php5-ldap \
            php5-mysql \
            python-pygments \
            sendmail \
            subversion \
            tar \
            sudo \
        && apt-get clean && rm -rf /var/lib/apt/lists/*

# For some reason phabricator doesn't have tagged releases. To support
# repeatable builds use the latest SHA
ADD     download.sh /opt/download.sh

WORKDIR /opt

RUN    git clone https://github.com/phacility/libphutil.git						
RUN    git clone https://github.com/phacility/arcanist.git
RUN    git clone https://github.com/phacility/phabricator.git

# Setup apache
RUN     a2enmod rewrite
ADD     phabricator.conf /etc/apache2/sites-available/phabricator.conf
RUN     ln -s /etc/apache2/sites-available/phabricator.conf \
            /etc/apache2/sites-enabled/phabricator.conf && \
        rm -f /etc/apache2/sites-enabled/000-default.conf

# Setup phabricator
RUN     mkdir -p /opt/phabricator/conf/local /var/repo
ADD     local.json /opt/phabricator/conf/local/local.json
RUN     sed -e 's/post_max_size =.*/post_max_size = 32M/' \
          -e 's/upload_max_filesize =.*/upload_max_filesize = 32M/' \
          -e 's/;opcache.validate_timestamps=.*/opcache.validate_timestamps=0/' \
          -i /etc/php5/apache2/php.ini
RUN     ln -s /usr/lib/git-core/git-http-backend /opt/phabricator/support/bin
RUN     /opt/phabricator/bin/config set phd.user "root"
RUN     echo "www-data ALL=(ALL) SETENV: NOPASSWD: /opt/phabricator/support/bin/git-http-backend" >> /etc/sudoers

ADD     default-ssl.conf /etc/apache2/sites-available/default-ssl.conf
ADD     server.crt /tmp/
ADD     server.key /tmp/
RUN     a2ensite default-ssl 
RUN     a2enmod ssl 
RUN     service apache2 restart

RUN 	apt-get update
RUN	yes  | apt-get install curl python-software-properties
RUN	curl -sL https://deb.nodesource.com/setup_7.x | sudo bash -
RUN 	apt-get install nodejs
RUN	npm install ws
RUN	sudo adduser test
#RUN 	chmod go+w /var/tmp/aphlict/
RUN 	whoami
USER 	test
RUN 	whoami
RUN	phabricator/bin/aphlict start
RUN	apt-get update
USER 	root
RUN 	export PATH="$PATH:/opt/arcanist/bin"
RUN	sudo apt-get install nano 
#RUN 	yes | sudo apt-get install postfix


EXPOSE  80
ADD     entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
CMD     ["start-server"]
