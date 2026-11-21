# The CV's name, contact details and summary. There is only ever one.
class Profile < ApplicationRecord
  Link = Data.define(:label, :url)
  LINK_LINE = %r{\A\s*(.+?)\s+((?:https?://|mailto:)\S+)\s*\z}i

  normalizes :name, :headline, :location, :email, with: ->(text) { text.strip }
  normalizes :links, :summary, :machine_note, with: ->(text) { text.gsub("\r\n", "\n").strip }
  normalizes :search_description, with: ->(text) { text.squish }

  validates :name, presence: { message: "Your name is shown at the top of every page." }
  validates :name, :headline, :location, :email, length: { maximum: 255 }
  validates :email, format: { with: URI::MailTo::EMAIL_REGEXP, message: "That doesn't look like an email address." }, allow_blank: true
  validates :search_description, length: { maximum: 300, message: "Keep this under 300 characters. Google shows about 155." }
  validates :machine_note, length: { maximum: 2000 }
  validate :links_are_well_formed

  def self.instance
    first || new(name: "Your Name")
  end

  # The headline up to its first separator: "Data Engineer · Python, SQL" gives "Data Engineer".
  def role
    headline.to_s.split(/\s*[·|,—–:]\s*|\s+-\s+/, 2).first.to_s.strip
  end

  # The links field: one per line, a label then the address.
  def link_list
    links.to_s.lines.filter_map do |line|
      Link.new(label: $1, url: $2) if line.chomp.match(LINK_LINE)
    end
  end

  private
    def links_are_well_formed
      bad = links.to_s.lines.map(&:strip).find { |line| line.present? && !line.match?(LINK_LINE) }
      errors.add(:links, %(This line needs a label and then an address starting with https://: "#{bad}")) if bad
    end
end
