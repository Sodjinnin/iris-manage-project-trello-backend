- installation: composer i
- config data base with env.dev
- php bin/console make:migration
- php bin/console doctrine:migrations:migrate
- generate jwt :
   - mkdir -p config/jwt
   - openssl genpkey -algorithm RSA -out config/jwt/private.pem -aes256
   - openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
- start server :  symfony serve
- doc: https://documenter.getpostman.com/view/4514748/2sB2j3BrPf


