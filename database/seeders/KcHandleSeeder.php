<?php

namespace Database\Seeders;

use App\Models\KcHandle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KcHandleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample KcHandle records
        KcHandle::create([
            'handle' => 'jon',
            'client_id' => '3d6ff64c-7b41-4b8d-a92c-bb51df27222e',
            'access_token' => "eyJhbGciOiJSUzI1NiIsImtpZCI6IjkzZjFkY2FjLTg5MjQtNDU3MS04ZWE5LWI3Mjc4MzY5Yzc5YiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE3NDA5OTEwNzExMjMsInN1YiI6IjYxYTFmZWJiZDFkMDkzMDAwODk0OTM2NyIsImFsZyI6IlJTMjU2IiwiaXNzIjoia2luZ3NjaGF0IiwiYXVkIjpbInNlbmRfY2hhdF9tZXNzYWdlIl0sImFjaWQiOiIzZDZmZjY0Yy03YjQxLTRiOGQtYTkyYy1iYjUxZGYyNzIyMmUiLCJjaWQiOiI2N2M1NWM0YzAzZmI5MTE2NmU3MmQ3YzQifQ.GrEnKdCN5UVyAlHpHksiQaqXUoGcbK5s22zb2ZBQR6R0Uwpey6KUnF683vrJU1oWWyvV8beFSd1Ra7tItII__5twRc93zaJ48n7pmBtg9GgP8fem-xT_dNu6WVt7u7QdeuOchgtG3g2SswJcvSTQ_rI39gpsnwlH2J0Fz9YXD-EQo_ApoW7ouNHeKcT0Vkl1zwacmXIur1hhz-_JHYBx8Lv4SP1hIL1oen6RUCcH0_74AzoLIvVrMxl1sv1kYizYGkM2CZd8AylqMVFKpvHbYh23r8KrEZqpjslvFP51tUDvLOK_RzMKTniXzSkPH3q51bBKL4CkFK-RGxihCvQhCA",
            'refresh_token' => "hgQ9TXH7p1jH32zkp1uy7oc8c93FsM2ablCStqgb8go="
        ]);
    }
}