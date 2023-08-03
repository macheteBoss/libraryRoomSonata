# libraryRoomSonata

# Инструкция

Клонируем репо-рий:
# git clone https://github.com/macheteBoss/libraryRoomSonata.git

Заходим в папку с проектом, открываем в терминале, выполняем:
# docker-compose up --build -d

Заходим в контейнер:
# docker exec -it sonataroom-app-php-cli bash

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
