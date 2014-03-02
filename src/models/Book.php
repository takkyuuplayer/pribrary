<?php
class Books extends Model
{
    public function save()
    {
        if($this->is_new()) {
            $this->orm->created_at = time();

        }
        if(!$this->number) {
            $max = ORM::for_table('books')
                ->where_equal('category_id', $this->category_id)
                ->max('number');
            $this->number = $max + 1;
        }
        $this->orm->updated_at = time();
        parent::save();
    }
}
