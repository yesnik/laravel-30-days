# 30 days with Laravel

Learn: https://laracasts.com/series/30-days-to-learn-laravel-11/episodes/13

## 12. Pivot Tables and BelongsToMany Relationships

Create Model with Factory and Migration:

```
php artisan make:model Tag -mf
```

In a migration add cascade deleting:

```php
$table->foreignIdFor(\App\Models\Job::class, 'job_listing_id')
    ->constrained()->cascadeOnDelete();
```

To enable foreign keys on SQLite:

```sql
PRAGMA foreign_keys=ON;
```

We can specify non standard column on the foreign key relation:

```php
class Job extends Model
{
    // ...
    public function tags()
    {
        return $this->belongsToMany(Tag::class, foreignPivotKey: 'job_listing_id');
    }
}

class Tag extends Model
{
    // ...
    public function jobs()
    {
        return $this->belongsToMany(Job::class, relatedPivotKey: 'job_listing_id');
    }
}
```

We can attach a job to a tag:

```php
$tag = App\Models\Tag::find(3);
$tag->jobs()->attach(App\Models\Job::find(2));

// Reload data from DB
$tag->jobs()->get();
```

Get titles of selected jobs:

```php
$tag->jobs()->get()->pluck('title');
```
