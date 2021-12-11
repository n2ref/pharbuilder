## Usage
php pharbuilder.phar [OPTIONS]

## Required arguments
    -s  --source Source path
## Optional arguments
    -o  --outfile   Out phar file name
    -c  --compress  Compress type (GZ, BZ2), NONE - default
    -b  --bootstrap Bootstrap phar file
    -h  --help      Help message
    
## Example of usage
```
php pharbuilder.phar -s ~/source/my_lib -o ~/bin/my_lib.phar -b index.php -c gz
```
