# Coding Standards & Conventions

## Naming Conventions

### Current Inconsistencies

The following naming inconsistencies exist in the codebase:

| Entity | Inconsistent Names | Recommended |
|--------|-------------------|-------------|
| Mahasiswa name | `nama`, `nama_mahasiswa` | Use `nama_mahasiswa` consistently |
| User name | `nama_user`, `name` | Use `nama_user` consistently (already has accessor for `name`) |
| Kelas name | `nama_kelas`, `kelas` | Use `nama_kelas` for the field, `kelas` for display |

### Notes

1. **User Model**: Already has a `getNameAttribute()` accessor that provides backward compatibility for `$user->name` while the actual column is `nama_user`.

2. **Mahasiswa Model**: Uses `nama_mahasiswa` as the primary field. Some legacy code may reference `nama` - this should be standardized.

3. **Kelas Model**: Uses `nama_kelas` for the field name.

### Recommendations

If you want to fully standardize naming:

1. Create a migration to rename columns:
   ```php
   // Mahasiswa table - if 'nama' column exists and is different from 'nama_mahasiswa'
   $table->renameColumn('nama', 'nama_mahasiswa');
   ```

2. Update all views and controllers to use consistent naming

3. Use accessors for backward compatibility during transition

## Service Classes

### GradingService

Location: `app/Services/GradingService.php`

Provides common grading and evaluation calculation methods:

- `calculateGrade(?float $score): ?string` - Calculate letter grade from numeric score
- `convertAttendanceToValue(string $status): int` - Convert attendance status to numeric value
- `calculateFinalScore(...)` - Calculate final score from components
- `calculateProjectScore(...)` - Calculate project score (dosen + mitra)
- `calculateActivityScore(...)` - Calculate activity score (attendance + presentation)
- `getEvaluationSetting(string $key, $default)` - Get evaluation setting with fallback
- `formatScore(float $score, int $decimals = 2): float` - Format score for display

Usage:
```php
use App\Services\GradingService;

class MyController extends Controller
{
    public function __construct(
        private GradingService $gradingService
    ) {}

    public function calculate()
    {
        $grade = $this->gradingService->calculateGrade(85.5); // 'A'
    }
}
```

## Model Conventions

### Mass Assignment

All models should use `$fillable` instead of `$guarded` for security:

```php
// ❌ BAD - Allows all fields except 'id'
protected $guarded = ['id'];

// ✅ GOOD - Only allows specific fields
protected $fillable = [
    'nama_kelas',
    'periode_id',
];
```

### Type Declarations

All methods should have proper return type declarations:

```php
// Relationships
public function kelompoks(): HasMany
{
    return $this->hasMany(Kelompok::class);
}

// Accessors
public function getNamaAttribute(): string
{
    return $this->attributes['nama_mahasiswa'] ?? '';
}

// Custom methods
public function calculateScore(float $value): float
{
    return round($value, 2);
}
```

## Query Optimization

### Eager Loading

Always eager load relationships to prevent N+1 queries:

```php
// ❌ BAD - N+1 query problem
$kelompoks = Kelompok::all();
foreach ($kelompoks as $kelompok) {
    echo $kelompok->periode->nama; // Separate query for each kelompok
}

// ✅ GOOD - Eager loading
$kelompoks = Kelompok::with('periode')->get();
foreach ($kelompoks as $kelompok) {
    echo $kelompok->periode->nama; // No additional queries
}
```

### Select Specific Columns

When you don't need all columns, select only what's needed:

```php
// ✅ GOOD - Only select needed columns
$users = User::select('id', 'uuid', 'nama_user', 'email')->get();
```

## Security Best Practices

1. **Never use `$guarded = []`** - This allows mass assignment for ALL fields
2. **Always validate user input** - Use Form Request classes or validation rules
3. **Use route model binding with UUID** - Prevents enumeration attacks
4. **Never expose debug routes in production** - Remove or protect with auth middleware
5. **Keep sensitive routes protected** - Use appropriate middleware

## Testing

Before committing changes:

1. Run Pint: `vendor/bin/pint --dirty`
2. Run tests: `php artisan test`
3. Check for N+1 queries in your logs
4. Verify all forms still work after changing `$fillable`
