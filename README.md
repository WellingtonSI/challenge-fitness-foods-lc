# PHP Challenge 20200916

# Introdução
Este desafio tem como objetivo dar suporte a equipe de nutricionistas da empresa Fitness Foods LC para que eles possam revisar de maneira rápida a informação nutricional dos alimentos que os usuários publicam pela aplicação móvel.

# Processo
Neste desafio, foram abordadas algumas complexidades ao lidar com arquivos muito grandes. Foi necessário recorrer a abordagens como abrir o arquivo e ler byte a byte para processar todas as informações e entender a estrutura interna dos dados. Além disso, compreender o tempo que o script levou para concluir todo o processo, bem como a quantidade de memória utilizada.

# Como instalar e usar o projeto
Neste projeto existe a possilibidade de utilizam de docker compose para criação do ambiente, basta dentro da raiz do projeto aplicar no comando no terminal `docker-compose up`, lembrando que é necessário ter o docker compose instalado em sua máquina.
Como o projeto foi feito em laravel, será necessário seguir todo o processo natural desse framework

- Entre no terminal no docker ou no qual foi de sua escolha e rode o comando `composer install` (instalar o composer caso não tenha instalado pelo docker)
- Faça a cópia do arquivo `.env.example` e renomeie para `.env `
- Logo após, rode o comando `php artisan key:generate`.
- Faças as configurações necessárias no arquivo .env

Para rodar a Schedules e fazer as importações dos producst, basta ir no terminal e rodar o comando `php artisan schedule:work`

Para testar as rotas basta rodar o comando `php artisan serve`

Em caso de dúvida olhar a documentação do laravel, principalmente para a Schedules.