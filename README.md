# libraryRoomSonata

# Инструкция

Клонируем репо-рий:
# git clone https://github.com/macheteBoss/libraryRoomSonata.git

Заходим в папку с проектом, открываем в терминале, выполняем:
# docker-compose up --build -d

Поставим права на папку для успешной загрузки изображений:
# chmod -R 777 app/public

Заходим в контейнер:
# docker exec -it sonataroom-php-cli bash

Выполняем команды:

# composer install
# php bin/console doctrine:migrations:migrate
# php bin/console doctrine:schema:update --force
# php bin/console acl:init
# php bin/console sonata:admin:setup-acl
# php bin/console doctrine:fixtures:load


Создаём пользователя для входа в админку:
# php bin/console fos:user:create --super-admin


Сбросить кеш:
# php bin/console cache:clear


# Где смотреть

Результат sql запроса:

localhost:8090/


SonataAdmin:

localhost:8090/admin
