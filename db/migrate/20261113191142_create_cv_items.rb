class CreateCvItems < ActiveRecord::Migration[8.1]
  def change
    create_table :cv_items do |t|
      t.string :section, null: false
      t.string :title, null: false, default: ""
      t.string :subtitle, null: false, default: ""
      t.string :dates, null: false, default: ""
      t.string :meta, null: false, default: ""
      t.string :url, null: false, default: ""
      t.text :body, null: false, default: ""
      t.integer :position, null: false, default: 0

      t.timestamps
    end
    add_index :cv_items, [ :section, :position ]
  end
end
