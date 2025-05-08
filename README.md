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

## 13. Eager Loading and the N+1 Problem

To see N+1 problem install Laravel DebugBar:

```
composer require barryvdh/laravel-debugbar --dev
```
Visit http://example.test/jobs and see many SQL queries at the Debug bar.

To solve N+1 add eager loading at `routes/web.php`:

```php
$jobs = Job::with('employer')->get();
```

We can disable lazy loading in the app, file `app/Providers/AppServiceProvider.php`:

```php
public function boot(): void
{
    Model::preventLazyLoading();
}
```

This setting will help us to see N+1 problem - an error will occur:
*Attempted to lazy load [employer] on model [App\Models\Job] but lazy loading is disabled.*

## 14. All You Need to Know About Pagination

We can add pagination:

```php
$jobs = Job::with('employer')->paginate(3);
```

We can display page 2: http://example.test/jobs?page=2

To display pagination menu in the template: `{{ $jobs->links() }}`

It looks great because Laravel thinks that you're using Tailwind by default.

To copy templates for pagination from vendor to app: 

```
php artisan vendor:publish
```
Select `laravel-pagination` tag.
It will copy files to `resources/views/vendor/pagination`.

Also Laravel supports https://semantic-ui.com/ .

To enable Bootstrap 5 for pagination, edit `app/Providers/AppServiceProvider.php`:

```php
    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
```

To enable simple pagination with Next, Previous buttons:

```php
$jobs = Job::with('employer')->simplePaginate(3);
```

To enable cursor pagination with Next, Previous buttons and strange links like 
this http://example.test/jobs?cursor=eyJqb2JfbGlzdGluZ3MuaWQiOjMsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0 :

```php
$jobs = Job::with('employer')->cursorPaginate(3);
```

## 15. Understanding Database Seeders

Drop database and apply all migrations with seeds:

```
php artisan migrate:fresh --seed
```

Apply seeds to DB: `php artisan db:seed`

Add seeder to `database\seeders\DatabaseSeeder.php`:

```php
Job::factory(200)->create();
```

We can make a new seeder: `php artisan make:seeder` and define `JobSeeder`:

```php
class JobSeeder extends Seeder
{
    public function run(): void
    {
        Job::factory(200)->create();
    }
}
```
After this we can call this seeder at `database\seeders\DatabaseSeeder.php`:

```php
$this->call(JobSeeder::class);
```

We can seed DB only with one seeder: 

```
php artisan db:seed --class=JobSeeder
```

## 16. Forms and CSRF Explained (with Examples)

Order of routes at `routes/web.php` matters. You should define routes from specific to general:

- '/jobs/create'
- '/jobs/{id}'

Move job's templates to one folder:

- `/resources/view/jobs/index.blade.php` - display list of jobs
- `/resources/view/jobs/show.blade.php` - display a single job
- `/resources/view/jobs/create.blade.php` - create a single job

We can use this template in the `web.php`:

```php
Route::get('/contact', function () {
    return view('contact');
});
```

Form Layouts: https://tailwindcss.com/plus/ui-blocks/application-ui/forms/form-layouts

Add CSRF token to form:

```html
<form method="POST" action="/jobs">
  @csrf
</form>
```

This will create a hidden input in the form:

```html
<input type="hidden" name="_token" value="GDA2nUT6OJCEGAlKTdi3CDxhCyB0ktU3Kw70GH3t" autocomplete="off">
```

We can get all submitted data:

```php
Route::post('/jobs', function() {
    dd(request()->all());
});
```

Get submitted value of "title": `request('title')`

Order by created_at in DESC order:

```php
$jobs = Job::with('employer')->latest()->simplePaginate(3);
```

To disable fillable feature add this property to `Job` model:

```php
protected $guarded = []; 
```

To remove mass asigment protection - `app/Providers/AppServiceProvider.php`

```php
public function boot(): void
{
    Model::unguard();
}
```

## 17. Always Validate. Never Trust the User

We can create component `resources\views\Components\button.blade.php`:

```php
<a {{ $attributes->merge(['class' => 'relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 dark:active:bg-gray-700 dark:focus:border-blue-800']) }}>{{ $slot }}</a>
```

Use it at layout file:

```php
<x-button href="/jobs/create">Create Job</x-button> 
```

Add validation rules to `routes/web.php`:

```php
    request()->validate([
        'title' => ['required', 'min:3'],
        'salary' => ['required'],
    ]);
```

Display validation message for a field:

```php
@error('title')
    <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
@enderror
```

## 18. Editing, Updating, and Deleting a Resource

Use this method to throw exception if model not found:

```php
$job = Job::findOrFail($id);
```

Update fields of a model:

```php
$job->update([
    'title' => request('title'),
    'salary' => request('salary'),
]);
```

We can add to a form `@method('PATCH')`. It will create a hidden input:

```html
<input type="hidden" name="_method" value="PATCH">
```

We can add `form` attribute to a button to submit a form with delete action:

```html
<button form="delete-form" class="text-red-500 text-sm font-bold">Delete</button>
```
This button will submit a hidden form:

```html
<form action="/jobs/{{ $job->id }}" id="delete-form" method="POST" class="hidden">
  @csrf
  @method('DELETE')
</form>
```


