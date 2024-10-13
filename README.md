# Gomaa


<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## About Project Package Test
Project Package Test

- [My Package in Packagist](https://packagist.org/users/mohamedahmedgomaa/packages).
- [Learning Create Simple Package](https://medium.com/@francismacugay/build-your-own-laravel-package-in-10-minutes-using-composer-867e8ef875dd).

## How To Create Package In Project :
- composer require gomaa/test


## Update Project Package Test
Update

### after edit everything in the code :
- composer update.
- comment and push code.
- git tag "number version" Ex : 1.0.1.
- - git tag 1.0.1.
- -  git push -u origin --tags.


## Project Two 

- composer update
- config app file : add to providers 
- Gomaa\Test\TestServiceProvider::class
-  php artisan vendor:publish --provider="Gomaa\Test\TestServiceProvider" .
