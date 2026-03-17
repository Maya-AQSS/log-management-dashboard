<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\ErrorCode;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Comment::updateOrCreate(
            ['id' => 1],
            [
                'content' => 'Comment 1 de Admin',
                'user_id' => 1,
                'commentable_type' => ErrorCode::class,
                'commentable_id' => 1,
            ]
        );

        Comment::updateOrCreate(
            ['id' => 2],
            [
                'content' => 'Comment 2 de User',
                'user_id' => 2,
                'commentable_type' => ErrorCode::class,
                'commentable_id' => 1,
            ]
        );
    }
}
