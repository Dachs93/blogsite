module Admin
  class CvController < BaseController
    def show
      @items = CvItem.by_section
    end
  end
end
