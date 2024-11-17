FROM mariadb:latest

COPY ./docker-entrypoint-initdb.d/ /docker-entrypoint-initdb.d/

CMD ["mariadbd"]

EXPOSE 3306
