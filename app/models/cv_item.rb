# One entry on the CV. Each section uses only some of the columns, and
# SECTIONS says which, and what the admin form calls them.
class CvItem < ApplicationRecord
  SECTIONS = {
    "experience" => {
      heading: "Experience",
      item: "job",
      fields: { title: "Role", subtitle: "Company", dates: "Dates", meta: "Location and tools", body: "Highlights" },
      hints: {
        dates: "For example: 2022 – Present",
        meta: "For example: Dublin · Ruby, SQLite",
        body: 'Markdown. One bullet per line, starting with "- ".'
      }
    },
    "education" => {
      heading: "Education",
      item: "qualification",
      fields: { title: "Qualification", subtitle: "Institution", dates: "Dates", meta: "Notes" },
      hints: { meta: "Grade, thesis or focus. Optional." }
    },
    "skill" => {
      heading: "Skills",
      item: "skills",
      single: true, # One box for the whole section, not a list of entries.
      fields: { body: "Skills" },
      hints: { body: 'Markdown. One bullet per line, starting with "- ".' }
    },
    "project" => {
      heading: "Projects",
      item: "project",
      fields: { title: "Name", url: "Link", body: "Description", meta: "Built with" },
      hints: { url: "Optional. A full address starting with https://", body: "Markdown. A sentence or two." }
    }
  }.freeze

  FIELDS = %i[title subtitle dates meta url body].freeze

  normalizes(*FIELDS, with: ->(text) { text.gsub("\r\n", "\n").strip })

  validates :section, inclusion: { in: SECTIONS.keys }
  validates :title, :subtitle, length: { maximum: 255 }
  validates :dates, length: { maximum: 100 }
  validates :meta, :url, length: { maximum: 500 }
  validates :url, format: { with: %r{\Ahttps?://\S+\z}i, message: "Use a full address starting with https://" }, allow_blank: true
  validate :first_field_present

  before_validation :clear_unused_fields
  before_create { self.position = (CvItem.where(section: section).maximum(:position) || -1) + 1 }

  scope :ordered, -> { order(:position, :id) }

  # Entries grouped by section, in display order, with every section present.
  def self.by_section
    SECTIONS.keys.index_with { [] }.tap do |grouped|
      ordered.each { |item| grouped[item.section]&.push(item) }
    end
  end

  def definition = SECTIONS.fetch(section)

  # What the admin calls this entry: its title, or the section heading for a section without titles.
  def label = title.presence || definition[:heading]

  # Swap with the neighbour above ("up") or below ("down") in the section.
  def move(direction)
    siblings = CvItem.where(section: section).ordered.to_a
    index = siblings.index(self)
    swap = direction == "up" ? index - 1 : index + 1
    return if swap.negative? || swap >= siblings.size

    siblings[index], siblings[swap] = siblings[swap], siblings[index]
    transaction do
      siblings.each_with_index { |item, position| item.update_column(:position, position) }
    end
  end

  private
    def first_field_present
      return unless SECTIONS.key?(section)

      field = definition[:fields].keys.first
      errors.add(field, "This one is required.") if self[field].blank?
    end

    # Fields this section doesn't use are always stored empty.
    def clear_unused_fields
      return unless SECTIONS.key?(section)

      (FIELDS - definition[:fields].keys).each { |field| self[field] = "" }
    end
end
