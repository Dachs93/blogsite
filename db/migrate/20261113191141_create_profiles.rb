class CreateProfiles < ActiveRecord::Migration[8.1]
  def change
    create_table :profiles do |t|
      t.string :name, null: false
      t.string :headline, null: false, default: ""
      t.string :location, null: false, default: ""
      t.string :email, null: false, default: ""
      t.text :links, null: false, default: ""
      t.text :summary, null: false, default: ""
      t.string :search_description, null: false, default: ""
      t.text :machine_note, null: false, default: ""

      t.timestamps
    end
  end
end
