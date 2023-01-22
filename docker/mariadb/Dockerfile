FROM mariadb:latest

COPY ./docker-entrypoint-initdb.d/ /docker-entrypoint-initdb.d/

CMD ["mysqld"]

EXPOSE 3306
