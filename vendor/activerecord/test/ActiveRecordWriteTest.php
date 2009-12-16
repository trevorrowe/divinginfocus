<?php
include 'helpers/config.php';

class ActiveRecordWriteTest extends DatabaseTest
{
	public function test_save()
	{
		$venue = new Venue(array('name' => 'Tito'));
		$venue->save();
	}

	public function test_insert()
	{
		$author = new Author(array('name' => 'Blah Blah'));
		$author->save();
		$this->assert_not_null(Author::find($author->id));
	}

	public function test_save_auto_increment_id()
	{
		$venue = new Venue(array('name' => 'Bob'));
		$venue->save();
		$this->assert_true($venue->id > 0);
	}

	public function test_delete()
	{
		$author = Author::find(1);
		$author->delete();

		$this->assert_false(Author::exists(1));
	}

	public function test_delete_by_find_all()
	{
		$books = Book::all();

		foreach ($books as $model)
			$model->delete();

		$res = Book::all();
		$this->assert_equals(0,count($res));
	}

	public function test_update()
	{
		$book = Book::find(1);
		$new_name = 'new name';
		$book->name = $new_name;
		$book->save();

		$this->assert_same($new_name, $book->name);
		$this->assert_same($new_name, $book->name, Book::find(1)->name);
	}

	public function test_update_attributes()
	{
		$book = Book::find(1);
		$new_name = 'How to lose friends and alienate people'; // jax i'm worried about you
		$attrs = array('name' => $new_name);
		$book->update_attributes($attrs);

		$this->assert_same($new_name, $book->name);
		$this->assert_same($new_name, $book->name, Book::find(1)->name);
	}

	/**
	 * @expectedException ActiveRecord\UndefinedPropertyException
	 */
	public function test_update_attributes_undefined_property()
	{
		$book = Book::find(1);
		$book->update_attributes(array('name' => 'new name', 'invalid_attribute' => true , 'another_invalid_attribute' => 'blah'));
	}

	public function test_update_attribute()
	{
		$book = Book::find(1);
		$new_name = 'some stupid self-help book';
		$book->update_attribute('name', $new_name);

		$this->assert_same($new_name, $book->name);
		$this->assert_same($new_name, $book->name, Book::find(1)->name);
	}

	/**
	 * @expectedException ActiveRecord\UndefinedPropertyException
	 */
	public function test_update_attribute_undefined_property()
	{
		$book = Book::find(1);
		$book->update_attribute('invalid_attribute', true);
	}

	public function test_save_null_value()
	{
		$book = Book::first();
		$book->name = null;
		$book->save();
		$this->assert_true(Book::first()->name === null);
	}

	public function test_save_blank_value()
	{
		$book = Book::find(1);
		$book->name = '';
		$book->save();
		$this->assert_same('',Book::find(1)->name);
	}

	public function test_dirty_attributes()
	{
		$book = $this->make_new_book_and(false);
		$this->assert_equals(array('name','special'),array_keys($book->dirty_attributes()));
	}

	public function test_dirty_attributes_cleared_after_saving()
	{
		$book = $this->make_new_book_and();
		$this->assert_true(strpos($book->table()->last_sql,'(name,special)') !== false);
		$this->assert_equals(null,$book->dirty_attributes());
	}

	public function test_dirty_attributes_cleared_after_inserting()
	{
		$book = $this->make_new_book_and();
		$this->assert_equals(null,$book->dirty_attributes());
	}

	public function test_no_dirty_attributes_but_still_insert_record()
	{
		$book = new Book;
		$this->assert_equals(null,$book->dirty_attributes());
		$book->save();
		$this->assert_equals(null,$book->dirty_attributes());
		$this->assert_not_null($book->id);
	}

	public function test_dirty_attributes_cleared_after_updating()
	{
		$book = Book::first();
		$book->name = 'rivers cuomo';
		$book->save();
		$this->assert_equals(null,$book->dirty_attributes());
	}

	public function test_dirty_attributes_after_reloading()
	{
		$book = Book::first();
		$book->name = 'rivers cuomo';
		$book->reload();
		$this->assert_equals(null,$book->dirty_attributes());
	}

	public function test_dirty_attributes_with_mass_assignment()
	{
		$book = Book::first();
		$book->set_attributes(array('name' => 'rivers cuomo'));
		$this->assert_equals(array('name'), array_keys($book->dirty_attributes()));
	}

	public function test_timestamps_set_before_save()
	{
		$author = new Author;
		$author->save();
		$this->assert_not_null($author->created_at, $author->updated_at);

		$author->reload();
		$this->assert_not_null($author->created_at, $author->updated_at);
	}

	public function test_timestamps_updated_at_only_set_before_update()
	{
		$author = new Author();
		$author->save();
		$created_at = $author->created_at;
		$updated_at = $author->updated_at;
		sleep(1);

		$author->name = 'test';
		$author->save();

		$this->assert_not_null($author->updated_at);
		$this->assert_same($created_at, $author->created_at);
		$this->assert_not_equals($updated_at, $author->updated_at);
	}

	public function test_create()
	{
		$author = Author::create(array('name' => 'Blah Blah'));
		$this->assert_not_null(Author::find($author->id));
	}

	public function test_create_should_set_created_at()
	{
		$author = Author::create(array('name' => 'Blah Blah'));
		$this->assert_not_null($author->created_at);
	}

	/**
	 * @expectedException ActiveRecord\ActiveRecordException
	 */
	public function test_update_with_no_primary_key_defined()
	{
		Author::table()->pk = array();
		$author = Author::first();
		$author->name = 'blahhhhhhhhhh';
		$author->save();
	}

	/**
	 * @expectedException ActiveRecord\ActiveRecordException
	 */
	public function test_delete_with_no_primary_key_defined()
	{
		Author::table()->pk = array();
		$author = author::first();
		$author->delete();
	}

	public function test_inserting_with_explicit_pk()
	{
		$author = Author::create(array('author_id' => 9999, 'name' => 'blah'));
		$this->assert_not_null(Author::find($author->id));
	}

	/**
	 * @expectedException ActiveRecord\ReadOnlyException
	 */
	public function test_readonly()
	{
		$author = Author::first(array('readonly' => true));
		$author->save();
	}

	private function make_new_book_and($save=true)
	{
		$book = new Book();
		$book->name = 'rivers cuomo';
		$book->special = 1;

		if ($save)
			$book->save();

		return $book;
	}
};