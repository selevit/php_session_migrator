# PHP Session Migrator

Script for the migration between PHP session storages.

# Examples

Migrate from files to memcache:

```
php migrator.php --from=files --to=memcache
```

Migrate from memcache to files:

```
php migrator.php --from=memcache --to=files
```

## Supported session storages

- Files (default PHP)
- Memcache
