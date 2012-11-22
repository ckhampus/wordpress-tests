class TestServerApp < Sinatra::Base
  get '/' do
    erb :index
  end

  post '/' do
    erb :result, :locals => {
      :text => params['text-field'],
      :select => params['select-field'],
      :radio => params['radio-field'],
      :checkbox1 => params['checkbox-field-1'],
      :checkbox2 => params['checkbox-field-2']
    }
  end
end