
## Laravel App

### Requirements

* [Docker Setup on Ubuntu](https://github.com/danieltrolezi/laravel-app/blob/master/docs/01-setup/docker.md)

### Setting up the Environment

1. Build images
```
$ docker compose build
```

2. Run the containers
```
$ docker compose up -d
```

3. Run migrations
```
$ docker exec -it app bash
$ php artisan migrate
```

4. Set application key
```
$ php artisan key:generate --ansi
```

### Documentation

[Full documentation here](https://github.com/danieltrolezi/laravel-app/blob/master/docs/index.md)
