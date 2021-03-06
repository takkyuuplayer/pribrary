<?php
class Books extends Model
{
    public function save()
    {
        ORM::get_db()->exec('BEGIN EXCLUSIVE TRANSACTION');

        if($this->is_new()) {
            $this->orm->created_at = time();

        }
        $this->orm->updated_at = time();

        if(!$this->number) {
            $max = ORM::for_table('books')
                ->where_equal('category_id', $this->category_id)
                ->max('number');
            $this->number = $max + 1;
        }
        parent::save();
        ORM::get_db()->exec('COMMIT');
    }
}
