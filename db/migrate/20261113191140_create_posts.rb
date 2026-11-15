class CreatePosts < ActiveRecord::Migration[8.1]
  def change
    create_table :posts do |t|
      t.string :slug, null: false
      t.string :title, null: false
      t.string :summary, null: false, default: ""
      t.text :body, null: false, default: ""
      t.date :published_on, null: false
      t.boolean :draft, null: false, default: true

      t.timestamps
    end
    add_index :posts, :slug, unique: true
    add_index :posts, [ :draft, :published_on ]
  end
end
