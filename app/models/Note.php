<?php
class Note extends Model
{
    protected string $table = 'notes';
    protected array $fillable = ['tuition_center_id','student_id','class_id','title','content','note_type','visibility','created_by'];
}
