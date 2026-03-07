---
name: create-migration
description: Scaffold and implement a new Laravel migration. Usage: /create-migration <description_of_change>
---

Create a Laravel migration for: {{args}}

Steps:
1. Run `php artisan make:migration {{args}}` and capture the generated filename from the output.
2. Read the generated migration file from `database/migrations/`.
3. Implement the `up()` method with the schema changes described in the args. Always add a corresponding `down()` reversal.
4. Ask the user: "Run `php artisan migrate` now?"
