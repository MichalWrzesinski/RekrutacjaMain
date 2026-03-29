defmodule PhoenixApi.RateLimiter do
  use GenServer

  @table :phoenix_api_rate_limits
  @user_limit 5
  @user_window_seconds 10 * 60
  @global_limit 1000
  @global_window_seconds 60 * 60

  def start_link(_opts) do
    GenServer.start_link(__MODULE__, %{}, name: __MODULE__)
  end

  def allow_import(user_id) when is_integer(user_id) do
    GenServer.call(__MODULE__, {:allow_import, user_id})
  end

  @impl true
  def init(state) do
    :ets.new(@table, [:named_table, :set, :public, read_concurrency: true])
    {:ok, state}
  end

  @impl true
  def handle_call({:allow_import, user_id}, _from, state) do
    now = System.system_time(:second)

    user_key = {:user, user_id}
    global_key = :global

    user_timestamps = get_timestamps(user_key) |> keep_recent(now, @user_window_seconds)
    global_timestamps = get_timestamps(global_key) |> keep_recent(now, @global_window_seconds)

    cond do
      length(user_timestamps) >= @user_limit ->
        put_timestamps(user_key, user_timestamps)
        put_timestamps(global_key, global_timestamps)
        {:reply, {:error, :user_limit_exceeded}, state}

      length(global_timestamps) >= @global_limit ->
        put_timestamps(user_key, user_timestamps)
        put_timestamps(global_key, global_timestamps)
        {:reply, {:error, :global_limit_exceeded}, state}

      true ->
        put_timestamps(user_key, [now | user_timestamps])
        put_timestamps(global_key, [now | global_timestamps])
        {:reply, :ok, state}
    end
  end

  defp get_timestamps(key) do
    case :ets.lookup(@table, key) do
      [{^key, timestamps}] -> timestamps
      [] -> []
    end
  end

  defp put_timestamps(key, timestamps) do
    true = :ets.insert(@table, {key, timestamps})
    :ok
  end

  defp keep_recent(timestamps, now, window_seconds) do
    Enum.filter(timestamps, fn timestamp ->
      now - timestamp < window_seconds
    end)
  end
end
