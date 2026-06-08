# Employee Sync Implementation Guide (HR Portal Side)

## Overview
This document describes the HR portal side of the automated employee sync feature to `washinton_agent`.

## Components Implemented

### Observer: `app/Observers/EmployeeObserver.php`
Located in: `d:\laragon2\www\washinton_hr\app\Observers\EmployeeObserver.php`

**Functionality**:
- Listens to Employee model `created()` event → Full sync
- Listens to Employee model `updated()` event → Sync if key fields changed
- Non-blocking: Errors logged but don't interrupt HR operations
- Key fields monitored on update: first_name, last_name, email, phone, role_id

**Sync Payload**:
```json
{
  "employee_id": 1,
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "role_id": 5,
  "role_name": "Agent"
}
```

### Service Provider: `app/Providers/AppServiceProvider.php`
- Registers observer in `boot()` method
- Ensures observer is active for all Employee model operations

### Environment Variables
File: `.env`

**Required**:
```
HELLOTRANSPORT_BRIDGE_URL=https://hellotransport.com
HELLOTRANSPORT_BRIDGE_KEY=c689d5166a3fe6a0772ce020b14c2e0d980559f4de3e80617d617383ac592cf5
```

Must match the key in `washinton_agent/.env` as `HELLOTRANSPORT_BRIDGE_SHARED_KEY`

## Setup Checklist

- [x] Observer created at `app/Observers/EmployeeObserver.php`
- [x] Observer registered in `app/Providers/AppServiceProvider.php`
- [x] Bridge URL and key added to `.env`
- [x] HTTP calls use try-catch to prevent failures

## Testing the HR Observer

### 1. Create a New Employee
```bash
cd d:\laragon2\www\washinton_hr

php artisan tinker

// Create a test employee
$emp = new \App\Models\Employee();
$emp->first_name = 'Test';
$emp->last_name = 'Employee';
$emp->email = 'test.emp@example.com';
$emp->phone = '0300-1234567';
$emp->password = bcrypt('password');
$emp->employee_code = 'EMP001';
$emp->employment_type_id = 1;
$emp->employee_status_id = 1;
$emp->role_id = 1;
$emp->save();
```

### 2. Check Observer Execution
The observer should trigger HTTP call to washinton_agent.

Check logs:
```bash
tail -f storage/logs/laravel.log
```

Look for:
- "EmployeeObserver: Employee synced to washinton_agent" (success)
- "EmployeeObserver: Failed to sync employee" (error)

### 3. Verify in washinton_agent
```bash
cd d:\laragon2\www\washinton_agent

php artisan tinker
>>> \App\User::where('email', 'test.emp@example.com')->first();
```

Should return user with `hr_employee_id = 1`

## Update Sync Behavior

The observer only syncs on update if these fields change:
- `first_name`
- `last_name`
- `email`
- `phone`
- `role_id`

To test update sync:
```bash
php artisan tinker

$emp = \App\Models\Employee::find(1);
$emp->phone = '0300-9876543'; // Changed phone
$emp->save();
```

Check logs for sync message.

## Error Cases

### 1. Bridge Key Not Configured
**Log**: "HELLOTRANSPORT_BRIDGE_KEY not configured"
**Action**: Add to .env and restart queue (if async)

### 2. Bridge URL Unreachable
**Log**: "Failed to call bridge endpoint"
**Impact**: Employee still created in HR, sync just fails
**Resolution**: Check network, restart washinton_agent

### 3. Email Already Exists in washinton_agent
**Log**: "Bridge endpoint returned error"
**Response**: User updated instead of created (no duplicate)

### 4. Invalid Bridge Key
**Log**: "Bridge endpoint returned error 403"
**Check**: Keys match exactly between HR and Agent .env files

## Monitoring

### Log Locations
- washinton_hr: `storage/logs/laravel.log`
- washinton_agent: `storage/logs/laravel.log`

### Key Log Messages
```
SUCCESS:
  EmployeeObserver: Employee synced to washinton_agent

FAILURE:
  EmployeeObserver: Failed to sync employee to washinton_agent
  EmployeeObserver: Failed to call bridge endpoint
```

### Database Verification
```bash
php artisan tinker

// In washinton_hr, find employee
$emp = \App\Models\Employee::find(1);
echo $emp->email;

// In washinton_agent, find linked user
$user = \App\User::where('hr_employee_id', 1)->first();
echo $user->email;
```

Both should show same email.

## Integration Notes

### Created Event
- Fires when new employee record is created
- Full sync to washinton_agent
- Non-blocking

### Updated Event
- Fires when employee record is updated
- Checks if key fields changed
- Only syncs if changes detected
- Prevents unnecessary API calls

### Deletion
- **Note**: Not implemented yet
- **Future**: Could soft-delete user in washinton_agent

## Performance Impact

- Observer adds ~100ms per employee creation (HTTP call)
- Update sync only on relevant fields (minimal impact)
- Non-blocking: Errors don't affect HR operation
- Timeouts: 10 seconds per HTTP request

## Troubleshooting

### Observer Not Firing
**Symptom**: Employee created but no sync attempt
**Check**:
```bash
# Verify observer is registered
php artisan tinker
>>> \Illuminate\Support\Facades\Event::getListeners('eloquent.created: App\Models\Employee')
```

**Fix**: 
- Check AppServiceProvider.php has observer registration
- Restart application

### Sync Attempts Too Many Times
**Symptom**: Multiple sync requests for single employee
**Cause**: Update event also triggers observer
**Fix**: Review observer logic to skip unnecessary updates

### Bridge Key Mismatch
**Verify**:
```bash
# washinton_hr
grep HELLOTRANSPORT_BRIDGE_KEY .env

# washinton_agent
grep HELLOTRANSPORT_BRIDGE_SHARED_KEY .env
```

Must match exactly.

## Security Considerations

1. **Bridge Key**: Stored in .env, not in code or logs
2. **HTTP Only**: Use HTTPS in production (already configured)
3. **Validation**: Request validates all input fields
4. **Logging**: Sensitive data (passwords) not logged
5. **Timeout**: 10 seconds max per request prevents hangs

## Related Files

- Observer: `app/Observers/EmployeeObserver.php`
- Service Provider: `app/Providers/AppServiceProvider.php`
- Configuration: `.env`
- Model: `app/Models/Employee.php`
- Remote Endpoint: washinton_agent `/bridge/employee/sync`
